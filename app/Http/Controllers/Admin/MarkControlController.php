<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Services\AuditLogService;
use App\Services\MarkEntryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MarkControlController extends Controller
{
    public function __construct(
        protected MarkEntryService $markEntryService
    ) {}

    /**
     * Mark Entry Status & Monitoring Screen.
     */
    public function index(Request $request): View
    {
        $exams = Exam::orderBy('id', 'desc')->get();
        $selectedExamId = $request->filled('exam_id') ? $request->query('exam_id') : $exams->first()?->id;
        $selectedExam = $exams->firstWhere('id', $selectedExamId) ?? $exams->first();

        $classes = SchoolClass::where('status', 'active')->orderBy('display_order')->get();
        $classId = $request->query('class_id');
        $statusFilter = $request->query('status');

        $assignments = [];

        if ($selectedExam) {
            $query = TeacherAssignment::with(['teacher', 'schoolClass', 'section', 'subject'])
                ->where('academic_year_id', $selectedExam->academic_year_id)
                ->where('status', 'active')
                ->when($classId, fn ($q) => $q->where('class_id', $classId));

            $assignmentList = $query->get();

            foreach ($assignmentList as $assignment) {
                $marks = Mark::where('exam_id', $selectedExam->id)
                    ->where('subject_id', $assignment->subject_id)
                    ->whereHas('student', function ($q) use ($assignment) {
                        $q->where('class_id', $assignment->class_id)
                          ->where('section_id', $assignment->section_id);
                    })
                    ->get();

                $totalMarks = $marks->count();
                $assignmentStatus = 'pending';
                $submittedAt = null;

                if ($totalMarks > 0) {
                    $statuses = $marks->pluck('status')->unique()->toArray();

                    if (in_array(Mark::STATUS_LOCKED, $statuses)) {
                        $assignmentStatus = Mark::STATUS_LOCKED;
                    } elseif (in_array(Mark::STATUS_VERIFIED, $statuses)) {
                        $assignmentStatus = Mark::STATUS_VERIFIED;
                    } elseif (in_array(Mark::STATUS_SUBMITTED, $statuses)) {
                        $assignmentStatus = Mark::STATUS_SUBMITTED;
                    } elseif (in_array(Mark::STATUS_DRAFT, $statuses)) {
                        $assignmentStatus = Mark::STATUS_DRAFT;
                    }

                    $submittedAt = $marks->whereNotNull('submitted_at')->max('submitted_at');
                }

                if (!$statusFilter || $assignmentStatus === $statusFilter) {
                    $assignment->calculated_status = $assignmentStatus;
                    $assignment->marks_count = $totalMarks;
                    $assignment->submitted_at_date = $submittedAt;
                    $assignments[] = $assignment;
                }
            }
        }

        return view('results.admin.marks.index', compact(
            'exams',
            'selectedExam',
            'selectedExamId',
            'classes',
            'classId',
            'statusFilter',
            'assignments'
        ));
    }

    /**
     * View marks for a specific teacher assignment.
     */
    public function show(Exam $exam, TeacherAssignment $assignment): View
    {
        // Security: ensure the assignment belongs to the same academic year as the exam
        abort_if($assignment->academic_year_id !== $exam->academic_year_id, 403, 'The assignment does not belong to this examination.');

        $students = $this->markEntryService->getStudentsWithMarks($assignment, $exam->id);
        return view('results.admin.marks.show', compact('exam', 'assignment', 'students'));
    }

    /**
     * Verify marks for an assignment.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $this->markEntryService->verifyMarks(
            $request->exam_id,
            $request->class_id,
            $request->section_id,
            $request->subject_id,
            Auth::id()
        );

        AuditLogService::log('marks_verified', null, $request->only(['exam_id', 'class_id', 'section_id', 'subject_id']), Auth::id());

        return back()->with('success', 'Marks verified successfully.');
    }

    /**
     * Lock marks for an assignment.
     */
    public function lock(Request $request): RedirectResponse
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $this->markEntryService->lockMarks(
            $request->exam_id,
            $request->class_id,
            $request->section_id,
            $request->subject_id,
            Auth::id()
        );

        AuditLogService::log('marks_locked', null, $request->only(['exam_id', 'class_id', 'section_id', 'subject_id']), Auth::id());

        return back()->with('success', 'Marks locked successfully.');
    }

    /**
     * Unlock submitted marks (allows teacher to edit again).
     */
    public function unlock(Request $request): RedirectResponse
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $this->markEntryService->unlockMarks(
            $request->exam_id,
            $request->class_id,
            $request->section_id,
            $request->subject_id,
            Auth::id()
        );

        AuditLogService::log('marks_unlocked', null, $request->only(['exam_id', 'class_id', 'section_id', 'subject_id']), Auth::id());

        return back()->with('success', 'Marks unlocked successfully. Teacher can now modify the mark entries.');
    }
}
