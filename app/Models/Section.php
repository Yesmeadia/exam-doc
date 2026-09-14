<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'class_id',
        'name',
        'status',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'section_id');
    }

    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class, 'section_id');
    }

    public function awardRolls(): HasMany
    {
        return $this->hasMany(AwardRoll::class, 'section_id');
    }

    public function subjects(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'section_subjects', 'section_id', 'subject_id')
            ->withPivot('is_optional')
            ->withTimestamps();
    }

    public function compulsorySubjects(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->subjects()->wherePivot('is_optional', false);
    }

    public function optionalSubjects(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->subjects()->wherePivot('is_optional', true);
    }
}
