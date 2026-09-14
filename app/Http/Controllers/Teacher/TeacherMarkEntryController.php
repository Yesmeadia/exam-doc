<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveMarksRequest;
use App\Http\Requests\SubmitMarksRequest;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\TeacherAssignment;
use App\Services\MarkEntryService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TeacherMarkEntryController extends Controller
{
    public function __construct(
        protected MarkEntryService $markEntryService
    ) {}

    /**
     * Mark Entry View for assigned class, section, and subject.
     */
    public function entry(Request $request, TeacherAssignment $assignment): View|RedirectResponse
    {
        // 1. Authorize teacher
        $this->authorize('view', $assignment);

        $examId = $request->query('exam_id');
        if (!$examId) {
            // Find active/open exam
            $exam = Exam::whereIn('status', [Exam::STATUS_ACTIVE, Exam::STATUS_MARK_ENTRY_OPEN])
                ->where('academic_year_id', $assignment->academic_year_id)
                ->latest()
                ->first();

            if (!$exam) {
                $exam = Exam::where('academic_year_id', $assignment->academic_year_id)->latest()->first() ?? Exam::latest()->first();
            }
        } else {
            $exam = Exam::findOrFail($examId);
        }

        if (!$exam) {
            return redirect()->route('teacher.dashboard')
                ->withErrors(['error' => 'No active examination found for mark entry.']);
        }

        // 2. Fetch students and existing marks strictly ordered by ROLL NUMBER ASCENDING
        $students = $this->markEntryService->getStudentsWithMarks($assignment, $exam->id);

        // 3. Determine if marks are submitted/locked
        $firstMark = $students->first()?->current_mark;
        $isLocked = $firstMark && in_array($firstMark->status, [Mark::STATUS_SUBMITTED, Mark::STATUS_VERIFIED, Mark::STATUS_LOCKED]);
        $submissionStatus = $firstMark?->status ?? 'draft';

        return view('results.teacher.marks.entry', [
            'assignment' => $assignment->load(['schoolClass', 'section', 'subject', 'academicYear']),
            'exam' => $exam,
            'students' => $students,
            'isLocked' => $isLocked,
            'submissionStatus' => $submissionStatus,
            'submittedAt' => $firstMark?->submitted_at,
        ]);
    }

    /**
     * Save marks as draft.
     */
    public function saveDraft(SaveMarksRequest $request, TeacherAssignment $assignment): RedirectResponse
    {
        try {
            $this->markEntryService->saveDraft(
                $assignment,
                (int) $request->exam_id,
                $request->marks,
                Auth::id()
            );

            return back()->with('success', 'Draft marks saved successfully. You can return and continue editing.');
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Submit marks (final submission for teacher).
     */
    public function submit(SubmitMarksRequest $request, TeacherAssignment $assignment): RedirectResponse
    {
        try {
            $this->markEntryService->submitMarks(
                $assignment,
                (int) $request->exam_id,
                $request->marks,
                Auth::id()
            );

            return redirect()->route('teacher.marks.entry', [
                'assignment' => $assignment->id,
                'exam_id' => $request->exam_id,
            ])->with('success', 'Marks submitted successfully. The submission is now locked for verification.');
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
