<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'Draft';
    public const STATUS_ACTIVE = 'Active';
    public const STATUS_MARK_ENTRY_OPEN = 'Mark Entry Open';
    public const STATUS_MARK_ENTRY_CLOSED = 'Mark Entry Closed';
    public const STATUS_VERIFICATION = 'Verification';
    public const STATUS_LOCKED = 'Locked';
    public const STATUS_PUBLISHED = 'Published';
    public const STATUS_ARCHIVED = 'Archived';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_ACTIVE,
        self::STATUS_MARK_ENTRY_OPEN,
        self::STATUS_MARK_ENTRY_CLOSED,
        self::STATUS_VERIFICATION,
        self::STATUS_LOCKED,
        self::STATUS_PUBLISHED,
        self::STATUS_ARCHIVED,
    ];

    public function isOpenForMarkEntry(): bool
    {
        return in_array($this->status, [
            self::STATUS_ACTIVE,
            self::STATUS_MARK_ENTRY_OPEN,
            self::STATUS_VERIFICATION,
        ]);
    }

    protected $fillable = [
        'academic_year_id',
        'exam_name',
        'exam_code',
        'description',
        'start_date',
        'end_date',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class);
    }

    public function awardRolls(): HasMany
    {
        return $this->hasMany(AwardRoll::class);
    }
}
