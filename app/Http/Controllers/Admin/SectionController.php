<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSectionRequest;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SectionController extends Controller
{
    /**
     * Display a listing of sections with filtering and subject assignment summary.
     */
    public function index(Request $request): View
    {
        $classId = $request->query('class_id');
        $search = $request->query('search');
        $status = $request->query('status');

        $classes = SchoolClass::where('status', 'active')->orderBy('display_order')->orderBy('name')->get();

        $sections = Section::with(['schoolClass', 'compulsorySubjects', 'optionalSubjects'])
            ->withCount('students')
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->when($status && $status !== 'all', fn($q) => $q->where('status', $status))
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('class_id')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        // All active subjects for the modal/drawer assignment
        $allSubjects = Subject::where('status', 'active')
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        return view('results.admin.sections.index', compact(
            'sections',
            'classes',
            'classId',
            'search',
            'status',
            'allSubjects'
        ));
    }

    /**
     * Store a newly created section and optionally sync subjects.
     */
    public function store(StoreSectionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $existing = Section::withTrashed()
            ->where('class_id', $data['class_id'])
            ->where('name', $data['name'])
            ->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            $existing->update([
                'status' => $data['status'] ?? 'active',
            ]);
            $section = $existing;
        } else {
            $section = Section::create([
                'class_id' => $data['class_id'],
                'name' => $data['name'],
                'status' => $data['status'] ?? 'active',
            ]);
        }

        $this->syncSubjects($section, $request->input('compulsory_subject_ids', []), $request->input('optional_subject_ids', []));

        return redirect()->route('admin.sections.index')
            ->with('success', "Section '{$section->name}' created successfully.");
    }

    /**
     * Update an existing section and optionally sync subjects.
     */
    public function update(StoreSectionRequest $request, Section $section): RedirectResponse
    {
        $data = $request->validated();

        $duplicate = Section::withTrashed()
            ->where('class_id', $data['class_id'] ?? $section->class_id)
            ->where('name', $data['name'])
            ->where('id', '!=', $section->id)
            ->first();

        if ($duplicate) {
            return redirect()->back()->with('error', "Another section named '{$data['name']}' already exists for this class.");
        }

        $section->update([
            'class_id' => $data['class_id'] ?? $section->class_id,
            'name' => $data['name'],
            'status' => $data['status'] ?? $section->status,
        ]);

        if ($request->has('compulsory_subject_ids') || $request->has('optional_subject_ids')) {
            $this->syncSubjects($section, $request->input('compulsory_subject_ids', []), $request->input('optional_subject_ids', []));
        }

        return redirect()->route('admin.sections.index')
            ->with('success', "Section '{$section->name}' updated successfully.");
    }

    /**
     * Dedicated endpoint to assign compulsory and optional subjects to a section.
     */
    public function assignSubjects(Request $request, Section $section): RedirectResponse
    {
        $request->validate([
            'compulsory_subject_ids' => ['nullable', 'array'],
            'compulsory_subject_ids.*' => ['exists:subjects,id'],
            'optional_subject_ids' => ['nullable', 'array'],
            'optional_subject_ids.*' => ['exists:subjects,id'],
        ]);

        $this->syncSubjects(
            $section,
            $request->input('compulsory_subject_ids', []),
            $request->input('optional_subject_ids', [])
        );

        return redirect()->route('admin.sections.index')
            ->with('success', "Curriculum subjects assigned to section '{$section->name}' ({$section->schoolClass?->name}) successfully.");
    }

    /**
     * Display the specified section — gracefully redirects to sections index
     * with class + name filters pre-applied.
     */
    public function show(Section $section): RedirectResponse
    {
        return redirect()->route('admin.sections.index', [
            'class_id' => $section->class_id,
            'search'   => $section->name,
        ]);
    }

    /**
     * Remove the specified section from storage (soft delete).
     */
    public function destroy(Section $section): RedirectResponse
    {
        $sectionName = $section->name;
        if (!$section->trashed()) {
            $section->delete();

            AuditLogService::log('section_deleted', $section, [
                'name'     => $sectionName,
                'class_id' => $section->class_id,
            ], Auth::id());
        }

        if (url()->previous() && !str_contains(url()->previous(), 'sections/' . $section->id)) {
            return redirect()->back()->with('success', "Section '{$sectionName}' deleted successfully.");
        }

        return redirect()->route('admin.sections.index')
            ->with('success', "Section '{$sectionName}' deleted successfully.");
    }

    /**
     * Helper to sync compulsory (is_optional = false) and optional (is_optional = true) subjects.
     */
    protected function syncSubjects(Section $section, array $compulsoryIds = [], array $optionalIds = []): void
    {
        $syncData = [];

        foreach ($compulsoryIds as $subId) {
            if ($subId) {
                $syncData[(int) $subId] = ['is_optional' => false];
            }
        }

        foreach ($optionalIds as $subId) {
            if ($subId && !isset($syncData[(int) $subId])) {
                $syncData[(int) $subId] = ['is_optional' => true];
            }
        }

        $section->subjects()->sync($syncData);
    }
}
