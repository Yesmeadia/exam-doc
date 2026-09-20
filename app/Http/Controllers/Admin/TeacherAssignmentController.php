<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherAssignmentRequest;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Notifications\TeacherAssignmentNotification;
use App\Services\TeacherAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class TeacherAssignmentController extends Controller
{
    public function __construct(
        protected TeacherAssignmentService $assignmentService
    ) {}

    public function index(Request $request): View
    {
        $academicYearId = $request->query('academic_year_id');
        $teacherId = $request->query('teacher_id');
        $classId = $request->query('class_id');

        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $teachers = User::role('teacher')->where('status', 'active')->orderBy('name')->get();
        $classes = SchoolClass::where('status', 'active')->orderBy('display_order')->get();

        $assignments = TeacherAssignment::with(['teacher', 'academicYear', 'schoolClass', 'section', 'subject'])
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->when($teacherId, fn ($q) => $q->where('teacher_id', $teacherId))
            ->when($classId, fn ($q) => $q->where('class_id', $classId))
            ->orderBy('class_id')
            ->orderBy('section_id')
            ->paginate(20);

        return view('results.admin.assignments.index', compact(
            'assignments',
            'academicYears',
            'teachers',
            'classes',
            'academicYearId',
            'teacherId',
            'classId'
        ));
    }

    public function create(): View
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $teachers = User::role('teacher')->where('status', 'active')->orderBy('name')->get();
        $classes = SchoolClass::with([
            'sections' => fn ($q) => $q->where('status', 'active')->orderBy('name'),
            'sections.subjects' => fn ($q) => $q->where('subjects.status', 'active')->orderBy('display_order')->orderBy('name'),
            'subjects' => fn ($q) => $q->where('subjects.status', 'active')->orderBy('display_order')->orderBy('name'),
        ])->where('status', 'active')->orderBy('display_order')->get();

        $classesPayload = $classes->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'code' => $c->code,
                'subjects' => $c->subjects->map(fn ($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'code' => $s->code,
                    'maximum_marks' => (float) $s->maximum_marks,
                    'is_optional' => (bool) ($s->pivot->is_elective ?? false),
                ])->values(),
                'sections' => $c->sections->map(function ($sec) use ($c) {
                    $hasCustomSubjects = $sec->subjects->isNotEmpty();
                    $assignedSubjects = $hasCustomSubjects ? $sec->subjects : $c->subjects;

                    return [
                        'id' => $sec->id,
                        'name' => $sec->name,
                        'has_custom_subjects' => $hasCustomSubjects,
                        'subjects' => $assignedSubjects->map(fn ($s) => [
                            'id' => $s->id,
                            'name' => $s->name,
                            'code' => $s->code,
                            'maximum_marks' => (float) $s->maximum_marks,
                            'is_optional' => (bool) ($s->pivot->is_optional ?? $s->pivot->is_elective ?? false),
                        ])->values(),
                    ];
                })->values(),
            ];
        });

        return view('results.admin.assignments.create', compact(
            'academicYears',
            'teachers',
            'classes',
            'classesPayload'
        ));
    }

    public function store(StoreTeacherAssignmentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $sectionIds = $request->input('section_ids', []);
        if (empty($sectionIds) && $request->filled('section_id')) {
            $sectionIds = [(int) $request->input('section_id')];
        }

        $assignments = $this->assignmentService->assignMultipleSections($data, $sectionIds, Auth::id());
        $firstAssignment = $assignments->first();
        if ($firstAssignment) {
            $firstAssignment->load(['teacher', 'academicYear', 'schoolClass', 'section', 'subject']);
        }

        $emailSent = false;
        if ($firstAssignment && $firstAssignment->teacher && $firstAssignment->teacher->email) {
            try {
                $firstAssignment->teacher->notify(new TeacherAssignmentNotification($firstAssignment));
                $emailSent = true;
            } catch (\Throwable $e) {
                Log::error("Teacher assignment notification email failed for {$firstAssignment->teacher->email}: " . $e->getMessage());
            }
        }

        $count = $assignments->count();
        $sectionNames = $assignments->pluck('section.name')->filter()->join(', ');
        $subjectName = $firstAssignment?->subject?->name ?? 'selected subject';
        $teacherName = $firstAssignment?->teacher?->name ?? 'Teacher';

        $message = "Assigned {$teacherName} to {$count} section(s)" . ($sectionNames ? " ({$sectionNames})" : '') . " for {$subjectName} successfully.";
        if ($emailSent) {
            $message .= " Assignment notification email dispatched to {$firstAssignment->teacher->email}.";
        }

        return redirect()->route('admin.assignments.index')->with('success', $message);
    }

    public function destroy(TeacherAssignment $assignment): RedirectResponse
    {
        $this->assignmentService->remove($assignment, Auth::id());
        return redirect()->route('admin.assignments.index')->with('success', 'Teacher assignment removed successfully.');
    }
}
