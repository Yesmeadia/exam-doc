<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\TeacherAssignment;
use App\Services\AcademicYearService;
use App\Services\StudentSubjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TeacherDashboardController extends Controller
{
    public function __construct(
        protected AcademicYearService $academicYearService,
        protected StudentSubjectService $studentSubjectService
    ) {}

    public function index(Request $request): View
    {
        $teacher = Auth::user();
        $activeYear = $this->academicYearService->getActiveYear();

        // Get exams that are currently open for mark entry or active
        $exams = Exam::whereIn('status', [
            Exam::STATUS_ACTIVE,
            Exam::STATUS_MARK_ENTRY_OPEN,
            Exam::STATUS_VERIFICATION,
        ])
        ->when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id))
        ->orderBy('id', 'desc')
        ->get();

        // If no open/active exams in current year, fallback to any exams for current year, or all exams
        if ($exams->isEmpty()) {
            $exams = Exam::when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id))
                ->orderBy('id', 'desc')
                ->get();

            if ($exams->isEmpty()) {
                $exams = Exam::orderBy('id', 'desc')->get();
            }
        }

        $selectedExamId = $request->filled('exam_id') ? $request->query('exam_id') : $exams->first()?->id;
        $selectedExam = $exams->firstWhere('id', $selectedExamId) ?? $exams->first();

        // Teacher assignments: regular teacher sees own, super admin sees all
        $assignmentsQuery = TeacherAssignment::with(['teacher', 'schoolClass', 'section', 'subject'])
            ->where('status', 'active')
            ->when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id));

        if (!$teacher->hasRole('super-admin')) {
            $assignmentsQuery->where('teacher_id', $teacher->id);
        }

        $assignments = $assignmentsQuery->get();

        foreach ($assignments as $assignment) {
            // Count eligible students (honors Class 11/12 subject allocation)
            $assignment->student_count = $this->studentSubjectService->getEligibleStudentsForSubject(
                $assignment->academic_year_id,
                $assignment->class_id,
                $assignment->section_id,
                $assignment->subject_id
            )->count();

            // Check mark submission status if an exam is selected
            if ($selectedExam) {
                $statusRecord = Mark::where('exam_id', $selectedExam->id)
                    ->where(function ($q) use ($assignment) {
                        $q->where('teacher_assignment_id', $assignment->id)
                          ->orWhere(function ($sub) use ($assignment) {
                              $sub->where('subject_id', $assignment->subject_id)
                                  ->whereHas('student', function ($s) use ($assignment) {
                                      $s->where('class_id', $assignment->class_id)
                                        ->where('section_id', $assignment->section_id);
                                  });
                          });
                    })
                    ->pluck('status')
                    ->first();

                $assignment->current_status = $statusRecord ?? 'Not Started';
            } else {
                $assignment->current_status = 'No Exam Configured';
            }
        }

        return view('results.teacher.dashboard', compact(
            'teacher',
            'activeYear',
            'exams',
            'selectedExam',
            'selectedExamId',
            'assignments'
        ));
    }
}
