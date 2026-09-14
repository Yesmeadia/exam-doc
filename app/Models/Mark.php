<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mark extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_LOCKED = 'locked';

    protected $fillable = [
        'exam_id',
        'academic_year_id',
        'student_id',
        'subject_id',
        'teacher_assignment_id',
        'marks',
        'is_absent',
        'remarks',
        'status',
        'submitted_at',
        'verified_at',
        'locked_at',
        'unlocked_at',
        'verified_by',
        'locked_by',
        'unlocked_by',
    ];

    protected function casts(): array
    {
        return [
            'marks' => 'float',
            'is_absent' => 'boolean',
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
            'locked_at' => 'datetime',
            'unlocked_at' => 'datetime',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacherAssignment(): BelongsTo
    {
        return $this->belongsTo(TeacherAssignment::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function unlockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unlocked_by');
    }
}
