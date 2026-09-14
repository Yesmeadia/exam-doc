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
        $classes = SchoolClass::with('sections')->where('status', 'active')->orderBy('display_order')->get();
        $subjects = Subject::with('classes')->where('status', 'active')->orderBy('display_order')->orderBy('name')->get();

        return view('results.admin.assignments.create', compact('academicYears', 'teachers', 'classes', 'subjects'));
    }

    public function store(StoreTeacherAssignmentRequest $request): RedirectResponse
    {
        $assignment = $this->assignmentService->assign($request->validated(), Auth::id());
        $assignment->load(['teacher', 'academicYear', 'schoolClass', 'section', 'subject']);

        $emailSent = false;
        if ($assignment->teacher && $assignment->teacher->email) {
            try {
                $assignment->teacher->notify(new TeacherAssignmentNotification($assignment));
                $emailSent = true;
            } catch (\Throwable $e) {
                Log::error("Teacher assignment notification email failed for {$assignment->teacher->email}: " . $e->getMessage());
            }
        }

        $message = 'Teacher assigned successfully.';
        if ($emailSent) {
            $message .= " Assignment notification email dispatched to {$assignment->teacher->email}.";
        }

        return redirect()->route('admin.assignments.index')->with('success', $message);
    }

    public function destroy(TeacherAssignment $assignment): RedirectResponse
    {
        $this->assignmentService->remove($assignment, Auth::id());
        return redirect()->route('admin.assignments.index')->with('success', 'Teacher assignment removed successfully.');
    }
}
