<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Services\AcademicYearService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(
        protected AcademicYearService $academicYearService
    ) {}

    public function index(Request $request): View
    {
        $activeYear = $this->academicYearService->getActiveYear();
        $selectedYearId = $request->query('academic_year_id', $activeYear?->id);

        $academicYears = AcademicYear::orderBy('id', 'desc')->get();

        // Selected exam (default to active/most recent exam in year)
        $examsQuery = Exam::query();
        if ($selectedYearId) {
            $examsQuery->where('academic_year_id', $selectedYearId);
        }
        $exams = $examsQuery->orderBy('id', 'desc')->get();
        if ($exams->isEmpty()) {
            $exams = Exam::orderBy('id', 'desc')->get();
        }

        $selectedExamId = $request->filled('exam_id') ? $request->query('exam_id') : $exams->first()?->id;
        $selectedExam = $exams->firstWhere('id', $selectedExamId) ?? $exams->first();

        // Stats counts
        $totalStudents = Student::query()
            ->when($selectedYearId, fn ($q) => $q->where('academic_year_id', $selectedYearId))
            ->where('status', 'active')
            ->count();

        $totalTeachers = User::role('teacher')->where('status', 'active')->count();
        $totalClasses = SchoolClass::where('status', 'active')->count();
        $totalSubjects = Subject::where('status', 'active')->count();

        // Mark Entry Progress
        $assignments = TeacherAssignment::with(['teacher', 'schoolClass', 'section', 'subject'])
            ->when($selectedYearId, fn ($q) => $q->where('academic_year_id', $selectedYearId))
            ->where('status', 'active')
            ->get();

        $totalAssignments = $assignments->count();
        $submittedCount = 0;
        $pendingAssignments = [];

        if ($selectedExam) {
            foreach ($assignments as $assignment) {
                $statusRecord = Mark::where('exam_id', $selectedExam->id)
                    ->where('subject_id', $assignment->subject_id)
                    ->whereHas('student', function ($q) use ($assignment) {
                        $q->where('class_id', $assignment->class_id)
                          ->where('section_id', $assignment->section_id);
                    })
                    ->pluck('status')
                    ->first();

                $assignment->mark_status = $statusRecord ?? 'pending';

                if (in_array($assignment->mark_status, [Mark::STATUS_SUBMITTED, Mark::STATUS_VERIFIED, Mark::STATUS_LOCKED])) {
                    $submittedCount++;
                } else {
                    $pendingAssignments[] = $assignment;
                }
            }
        }

        $submittedPercent = $totalAssignments > 0 ? round(($submittedCount / $totalAssignments) * 100, 1) : 0;
        $pendingPercent = 100 - $submittedPercent;

        return view('results.admin.dashboard', [
            'activeYear' => $activeYear,
            'selectedYearId' => $selectedYearId,
            'academicYears' => $academicYears,
            'exams' => $exams,
            'selectedExam' => $selectedExam,
            'totalStudents' => $totalStudents,
            'totalTeachers' => $totalTeachers,
            'totalClasses' => $totalClasses,
            'totalSubjects' => $totalSubjects,
            'totalAssignments' => $totalAssignments,
            'submittedCount' => $submittedCount,
            'submittedPercent' => $submittedPercent,
            'pendingPercent' => $pendingPercent,
            'pendingAssignments' => $pendingAssignments,
        ]);
    }
}
