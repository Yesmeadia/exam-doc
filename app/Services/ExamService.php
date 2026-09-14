<?php

namespace App\Services;

use App\Models\Exam;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ExamService
{
    /**
     * Create an examination.
     */
    public function create(array $data, int $userId): Exam
    {
        $data['created_by'] = $userId;
        if (empty($data['exam_code'])) {
            $slug = Str::upper(Str::slug($data['exam_name'] ?? 'EXAM'));
            $slug = substr($slug, 0, 30);
            $data['exam_code'] = ($slug ?: 'EXAM') . '-' . strtoupper(Str::random(5));
        }

        $exam = Exam::create($data);

        AuditLogService::log('exam_created', $exam, [
            'exam_name' => $exam->exam_name,
            'exam_code' => $exam->exam_code,
            'status' => $exam->status,
        ], $userId);

        return $exam;
    }

    /**
     * Update an examination.
     */
    public function update(Exam $exam, array $data, ?int $userId = null): Exam
    {
        if (empty($data['exam_code'])) {
            unset($data['exam_code']);
        }

        $oldStatus = $exam->status;
        $exam->update($data);

        AuditLogService::log('exam_updated', $exam, [
            'exam_name' => $exam->exam_name,
            'old_status' => $oldStatus,
            'new_status' => $exam->status,
        ], $userId);

        return $exam;
    }

    /**
     * Update exam status with logging.
     */
    public function updateStatus(Exam $exam, string $status, ?int $userId = null): Exam
    {
        $oldStatus = $exam->status;
        $exam->update(['status' => $status]);

        AuditLogService::log('exam_status_changed', $exam, [
            'from' => $oldStatus,
            'to' => $status,
        ], $userId);

        return $exam;
    }

    /**
     * Get all published exams for public display.
     */
    public function getPublishedExams(): Collection
    {
        return Exam::published()->with('academicYear')->orderBy('start_date', 'desc')->get();
    }
}
