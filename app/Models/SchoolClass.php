<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolClass extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'code',
        'display_order',
        'status',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'class_id');
    }

    public function allSections(): HasMany
    {
        return $this->hasMany(Section::class, 'class_id')->withTrashed();
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subjects', 'class_id', 'subject_id')
            ->withPivot('is_elective', 'academic_year_id')
            ->withTimestamps();
    }

    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class, 'class_id');
    }

    public function awardRolls(): HasMany
    {
        return $this->hasMany(AwardRoll::class, 'class_id');
    }

    /**
     * Determine if this class is 11th or 12th, where individual/elective subject allocation applies.
     * For all other classes (e.g. 1st to 10th), full class curriculum applies by default.
     */
    public function allowsIndividualSubjectAllocation(): bool
    {
        $name = strtolower(trim((string) $this->name));
        $code = strtolower(trim((string) $this->code));

        // Matches 11, 12, 11th, 12th, XI, XII as words/numbers
        if (preg_match('/\b(11|12|11th|12th|xi|xii)\b/i', $name) || preg_match('/\b(11|12|11th|12th|xi|xii)\b/i', $code)) {
            return true;
        }

        // Also check common aliases like "Plus One", "Plus Two", "+1", "+2", "HSS 1", "HSS 2"
        if (str_contains($name, 'plus one') || str_contains($name, 'plus two') ||
            str_contains($name, '+1') || str_contains($name, '+2') ||
            str_contains($name, 'hss 1') || str_contains($name, 'hss 2') ||
            str_contains($code, 'plus one') || str_contains($code, 'plus two') ||
            str_contains($code, '+1') || str_contains($code, '+2')) {
            return true;
        }

        return false;
    }
}
