<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'academic_year_id',
        'name',
        'code',
        'maximum_marks',
        'pass_marks',
        'display_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'maximum_marks' => 'float',
            'pass_marks' => 'float',
            'display_order' => 'integer',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subjects', 'subject_id', 'class_id')
            ->withPivot('is_elective', 'academic_year_id')
            ->withTimestamps();
    }

    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'section_subjects', 'subject_id', 'section_id')
            ->withPivot('is_optional')
            ->withTimestamps();
    }

    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class);
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class);
    }

    public function studentSubjects(): HasMany
    {
        return $this->hasMany(StudentSubject::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_subjects', 'subject_id', 'student_id')
            ->withPivot('is_special', 'status')
            ->withTimestamps();
    }

    public function awardRolls(): HasMany
    {
        return $this->hasMany(AwardRoll::class);
    }
}
