<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'name',
        'dob',
        'gender',
        'academic_year_id',
        'class_id',
        'section_id',
        'roll_no',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'roll_no' => 'integer',
        ];
    }

    /**
     * Interact with student_id attribute to enforce full uppercase and trimmed storage.
     */
    protected function studentId(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value !== null ? strtoupper($value) : null,
            set: fn (?string $value) => $value !== null ? strtoupper(trim($value)) : null,
        );
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class)->withTrashed();
    }

    public function studentSubjects(): HasMany
    {
        return $this->hasMany(StudentSubject::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'student_subjects', 'student_id', 'subject_id')
            ->withPivot('is_special', 'status')
            ->withTimestamps();
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class);
    }

    /**
     * Determine if this student is in 11th or 12th class, allowing individual subject allocation.
     * All other classes use full class subjects by default.
     */
    public function allowsIndividualSubjectAllocation(): bool
    {
        return $this->schoolClass?->allowsIndividualSubjectAllocation() ?? false;
    }
}
