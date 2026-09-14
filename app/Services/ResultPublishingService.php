<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Mark;
use App\Models\Student;
use App\Models\TeacherAssignment;

class ResultPublishingService
{
    /**
     * Get publishing readiness statistics for an exam.
     */
    public function getPublishingReadiness(Exam $exam): array
    {
        $academicYearId = $exam->academic_year_id;

        $totalStudents = Student::where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->count();

        $totalAssignments = TeacherAssignment::where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->count();

        // Marks counts for this exam
        $totalMarksEntered = Mark::where('exam_id', $exam->id)->count();

        $submittedCount = Mark::where('exam_id', $exam->id)
            ->where('status', Mark::STATUS_SUBMITTED)
            ->count();

        $verifiedCount = Mark::where('exam_id', $exam->id)
            ->where('status', Mark::STATUS_VERIFIED)
            ->count();

        $lockedCount = Mark::where('exam_id', $exam->id)
            ->where('status', Mark::STATUS_LOCKED)
            ->count();

        $draftCount = Mark::where('exam_id', $exam->id)
            ->where('status', Mark::STATUS_DRAFT)
            ->count();

        // Assignment status breakdown
        $pendingAssignments = 0;
        $submittedAssignments = 0;
        $assignments = TeacherAssignment::where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->get();

        foreach ($assignments as $assignment) {
            $hasSubmitted = Mark::where('exam_id', $exam->id)
                ->where('subject_id', $assignment->subject_id)
                ->whereIn('status', [Mark::STATUS_SUBMITTED, Mark::STATUS_VERIFIED, Mark::STATUS_LOCKED])
                ->exists();

            if ($hasSubmitted) {
                $submittedAssignments++;
            } else {
                $pendingAssignments++;
            }
        }

        $isReady = ($draftCount === 0 && $pendingAssignments === 0 && $totalMarksEntered > 0);

        return [
            'total_students' => $totalStudents,
            'total_assignments' => $totalAssignments,
            'total_marks' => $totalMarksEntered,
            'draft_count' => $draftCount,
            'submitted_count' => $submittedCount,
            'verified_count' => $verifiedCount,
            'locked_count' => $lockedCount,
            'pending_assignments' => $pendingAssignments,
            'submitted_assignments' => $submittedAssignments,
            'is_ready' => $isReady,
        ];
    }

    /**
     * Publish results for an examination.
     */
    public function publish(Exam $exam, int $adminId): void
    {
        $exam->update(['status' => Exam::STATUS_PUBLISHED]);

        AuditLogService::log('result_published', $exam, [
            'exam_id' => $exam->id,
            'exam_name' => $exam->exam_name,
        ], $adminId);
    }

    /**
     * Unpublish results (reverts status to Locked).
     */
    public function unpublish(Exam $exam, int $adminId): void
    {
        $exam->update(['status' => Exam::STATUS_LOCKED]);

        AuditLogService::log('result_unpublished', $exam, [
            'exam_id' => $exam->id,
            'exam_name' => $exam->exam_name,
        ], $adminId);
    }
}
