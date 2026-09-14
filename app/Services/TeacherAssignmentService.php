<?php

namespace App\Services;

use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class TeacherAssignmentService
{
    /**
     * Create a teacher assignment.
     */
    public function assign(array $data, int $assignedBy): TeacherAssignment
    {
        $data['assigned_by'] = $assignedBy;
        $data['status'] = $data['status'] ?? 'active';

        $assignment = TeacherAssignment::updateOrCreate(
            [
                'teacher_id' => $data['teacher_id'],
                'academic_year_id' => $data['academic_year_id'],
                'class_id' => $data['class_id'],
                'section_id' => $data['section_id'],
                'subject_id' => $data['subject_id'],
            ],
            $data
        );

        AuditLogService::log('teacher_assignment_created', $assignment, [
            'teacher_id' => $assignment->teacher_id,
            'class_id' => $assignment->class_id,
            'section_id' => $assignment->section_id,
            'subject_id' => $assignment->subject_id,
        ], $assignedBy);

        return $assignment;
    }

    /**
     * Remove an assignment.
     */
    public function remove(TeacherAssignment $assignment, int $userId): void
    {
        AuditLogService::log('teacher_assignment_removed', $assignment, [
            'teacher_id' => $assignment->teacher_id,
            'class_id' => $assignment->class_id,
            'section_id' => $assignment->section_id,
            'subject_id' => $assignment->subject_id,
        ], $userId);

        $assignment->delete();
    }

    /**
     * Get active assignments for a specific teacher.
     */
    public function getTeacherAssignments(int $teacherId, ?int $academicYearId = null): Collection
    {
        $query = TeacherAssignment::with(['academicYear', 'schoolClass', 'section', 'subject'])
            ->where('teacher_id', $teacherId)
            ->where('status', 'active');

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        return $query->get();
    }

    /**
     * Verify if a teacher owns a specific assignment.
     */
    public function teacherOwnsAssignment(int $teacherId, int $assignmentId): bool
    {
        return TeacherAssignment::where('id', $assignmentId)
            ->where('teacher_id', $teacherId)
            ->where('status', 'active')
            ->exists();
    }
}
