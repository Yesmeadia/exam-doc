<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(Request $request): View
    {
        $academicYearId = $request->query('academic_year_id');
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $classes = SchoolClass::where('status', 'active')->orderBy('display_order')->get();

        $subjects = Subject::with(['academicYear', 'classes'])
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->orderBy('display_order')
            ->orderBy('name')
            ->paginate(20);

        return view('results.admin.subjects.index', compact('subjects', 'academicYears', 'classes', 'academicYearId'));
    }

    public function create(): View
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $classes = SchoolClass::where('status', 'active')->orderBy('display_order')->get();

        return view('results.admin.subjects.create', compact('academicYears', 'classes'));
    }

    public function store(StoreSubjectRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['code'])) {
            $data['code'] = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::slug($data['name']));
        }
        $classIds = $data['class_ids'] ?? [];
        unset($data['class_ids']);

        $subject = Subject::create($data);

        if (!empty($classIds)) {
            $subject->classes()->sync($classIds);
        }

        return redirect()->route('admin.subjects.index')->with('success', 'Subject created successfully.');
    }

    public function edit(Subject $subject): View
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $classes = SchoolClass::where('status', 'active')->orderBy('display_order')->get();
        $selectedClassIds = $subject->classes->pluck('id')->toArray();

        return view('results.admin.subjects.edit', compact('subject', 'academicYears', 'classes', 'selectedClassIds'));
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): RedirectResponse
    {
        $data = $request->validated();
        $classIds = $data['class_ids'] ?? [];
        unset($data['class_ids']);

        $subject->update($data);
        $subject->classes()->sync($classIds);

        return redirect()->route('admin.subjects.index')->with('success', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->delete();
        return redirect()->route('admin.subjects.index')->with('success', 'Subject deleted successfully.');
    }
}
