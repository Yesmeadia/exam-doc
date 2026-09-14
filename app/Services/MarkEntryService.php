<?php

namespace App\Services;

use App\Models\Mark;
use App\Models\TeacherAssignment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MarkEntryService
{
    public function __construct(
        protected StudentSubjectService $studentSubjectService
    ) {}

    /**
     * Get students and existing marks for a teacher assignment and exam.
     * STRICT REQUIREMENT: Order students by ROLL NUMBER ASCENDING.
     */
    public function getStudentsWithMarks(TeacherAssignment $assignment, int $examId): Collection
    {
        // 1. Get eligible students strictly ordered by roll_no ASC
        $students = $this->studentSubjectService->getEligibleStudentsForSubject(
            $assignment->academic_year_id,
            $assignment->class_id,
            $assignment->section_id,
            $assignment->subject_id
        )->sortBy('roll_no', SORT_NUMERIC)->values();

        // 2. Fetch any existing marks for this exam and subject
        $existingMarks = Mark::where('exam_id', $examId)
            ->where('subject_id', $assignment->subject_id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        // 3. Attach mark to student object
        foreach ($students as $student) {
            $student->current_mark = $existingMarks->get($student->id);
        }

        return $students;
    }

    /**
     * Save marks as draft.
     */
    public function saveDraft(
        TeacherAssignment $assignment,
        int $examId,
        array $entries,
        int $userId
    ): void {
        $this->processMarks(
            $assignment,
            $examId,
            $entries,
            Mark::STATUS_DRAFT,
            $userId
        );

        AuditLogService::log('marks_saved_draft', $assignment, [
            'exam_id' => $examId,
            'assignment_id' => $assignment->id,
            'total_students' => count($entries),
        ], $userId);
    }

    /**
     * Submit marks (locks for teacher).
     */
    public function submitMarks(
        TeacherAssignment $assignment,
        int $examId,
        array $entries,
        int $userId
    ): void {
        $this->processMarks(
            $assignment,
            $examId,
            $entries,
            Mark::STATUS_SUBMITTED,
            $userId,
            true
        );

        AuditLogService::log('marks_submitted', $assignment, [
            'exam_id' => $examId,
            'assignment_id' => $assignment->id,
            'total_students' => count($entries),
        ], $userId);
    }

    /**
     * Internal processor for saving/submitting marks.
     */
    protected function processMarks(
        TeacherAssignment $assignment,
        int $examId,
        array $entries,
        string $targetStatus,
        int $userId,
        bool $isSubmission = false
    ): void {
        $maxMarks = (float) $assignment->subject->maximum_marks;

        $eligibleStudentIds = $this->studentSubjectService->getEligibleStudentsForSubject(
            $assignment->academic_year_id,
            $assignment->class_id,
            $assignment->section_id,
            $assignment->subject_id
        )->pluck('id')->flip()->toArray();

        DB::transaction(function () use ($assignment, $examId, $entries, $targetStatus, $userId, $isSubmission, $maxMarks, $eligibleStudentIds) {
            foreach ($entries as $studentId => $data) {
                // OWASP A01 Access Control: Verify student strictly belongs to this assignment's class/section/subject
                if (!isset($eligibleStudentIds[$studentId])) {
                    continue;
                }

                $isAbsent = !empty($data['is_absent']);
                $marksObtained = $isAbsent ? null : ($data['marks'] !== null && $data['marks'] !== '' ? (float) $data['marks'] : null);

                if (!$isAbsent && $marksObtained !== null) {
                    if ($marksObtained < 0 || $marksObtained > $maxMarks) {
                        throw new InvalidArgumentException("Marks for student ID {$studentId} ({$marksObtained}) exceeds maximum marks ({$maxMarks}).");
                    }
                }

                $existing = Mark::where('exam_id', $examId)
                    ->where('student_id', $studentId)
                    ->where('subject_id', $assignment->subject_id)
                    ->first();

                // If already submitted/verified/locked, prevent non-admin teacher edits unless unlocked
                if ($existing && in_array($existing->status, [Mark::STATUS_SUBMITTED, Mark::STATUS_VERIFIED, Mark::STATUS_LOCKED]) && !$isSubmission) {
                    continue;
                }

                $updateData = [
                    'academic_year_id' => $assignment->academic_year_id,
                    'teacher_assignment_id' => $assignment->id,
                    'marks' => $marksObtained,
                    'is_absent' => $isAbsent,
                    'remarks' => $data['remarks'] ?? null,
                    'status' => $targetStatus,
                ];

                if ($isSubmission) {
                    $updateData['submitted_at'] = now();
                }

                Mark::updateOrCreate(
                    [
                        'exam_id' => $examId,
                        'student_id' => $studentId,
                        'subject_id' => $assignment->subject_id,
                    ],
                    $updateData
                );
            }
        });
    }

    /**
     * Super Admin unlock marks so teacher can edit again.
     */
    public function unlockMarks(int $examId, int $classId, int $sectionId, int $subjectId, int $adminId): void
    {
        DB::transaction(function () use ($examId, $classId, $sectionId, $subjectId, $adminId) {
            Mark::where('exam_id', $examId)
                ->where('subject_id', $subjectId)
                ->whereHas('student', function ($q) use ($classId, $sectionId) {
                    $q->where('class_id', $classId)->where('section_id', $sectionId);
                })
                ->update([
                    'status' => Mark::STATUS_DRAFT,
                    'unlocked_at' => now(),
                    'unlocked_by' => $adminId,
                ]);

            AuditLogService::log('marks_unlocked', null, [
                'exam_id' => $examId,
                'class_id' => $classId,
                'section_id' => $sectionId,
                'subject_id' => $subjectId,
            ], $adminId);
        });
    }

    /**
     * Super Admin verify marks.
     */
    public function verifyMarks(int $examId, int $classId, int $sectionId, int $subjectId, int $adminId): void
    {
        DB::transaction(function () use ($examId, $classId, $sectionId, $subjectId, $adminId) {
            Mark::where('exam_id', $examId)
                ->where('subject_id', $subjectId)
                ->whereHas('student', function ($q) use ($classId, $sectionId) {
                    $q->where('class_id', $classId)->where('section_id', $sectionId);
                })
                ->update([
                    'status' => Mark::STATUS_VERIFIED,
                    'verified_at' => now(),
                    'verified_by' => $adminId,
                ]);

            AuditLogService::log('marks_verified', null, [
                'exam_id' => $examId,
                'class_id' => $classId,
                'section_id' => $sectionId,
                'subject_id' => $subjectId,
            ], $adminId);
        });
    }

    /**
     * Super Admin lock marks.
     */
    public function lockMarks(int $examId, int $classId, int $sectionId, int $subjectId, int $adminId): void
    {
        DB::transaction(function () use ($examId, $classId, $sectionId, $subjectId, $adminId) {
            Mark::where('exam_id', $examId)
                ->where('subject_id', $subjectId)
                ->whereHas('student', function ($q) use ($classId, $sectionId) {
                    $q->where('class_id', $classId)->where('section_id', $sectionId);
                })
                ->update([
                    'status' => Mark::STATUS_LOCKED,
                    'locked_at' => now(),
                    'locked_by' => $adminId,
                ]);

            AuditLogService::log('marks_locked', null, [
                'exam_id' => $examId,
                'class_id' => $classId,
                'section_id' => $sectionId,
                'subject_id' => $subjectId,
            ], $adminId);
        });
    }
}
