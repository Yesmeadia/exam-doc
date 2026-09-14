<?php

namespace App\Services;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentSubject;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StudentSubjectService
{
    /**
     * Get subjects for a student.
     * STRICT RULE:
     * - For classes other than 11th & 12th, always return the full subjects of the class / academic year by default.
     * - For 11th & 12th, return individual allocated elective subjects if defined, else fallback to full class subjects.
     */
    public function getSubjectsForStudent(Student $student): Collection
    {
        // For classes other than 11th & 12th, check section subjects first or use full subjects
        if (!$student->allowsIndividualSubjectAllocation()) {
            if ($student->section && $student->section->subjects()->exists()) {
                return $student->section->subjects()
                    ->where('subjects.status', 'active')
                    ->orderBy('subjects.display_order', 'asc')
                    ->orderBy('subjects.name', 'asc')
                    ->get();
            }
            return $this->getFullSubjectsForClass($student);
        }

        // For 11th & 12th classes: check individual allocations
        $allocatedSubjectIds = StudentSubject::where('student_id', $student->id)
            ->where('status', 'active')
            ->pluck('subject_id')
            ->toArray();

        if (!empty($allocatedSubjectIds)) {
            return Subject::whereIn('id', $allocatedSubjectIds)
                ->where('status', 'active')
                ->orderBy('display_order', 'asc')
                ->orderBy('name', 'asc')
                ->get();
        }

        // Fallback for 11th/12th students who don't have custom allocations yet
        if ($student->section && $student->section->subjects()->exists()) {
            return $student->section->subjects()
                ->where('subjects.status', 'active')
                ->orderBy('subjects.display_order', 'asc')
                ->orderBy('subjects.name', 'asc')
                ->get();
        }

        return $this->getFullSubjectsForClass($student);
    }

    /**
     * Helper to get all active curriculum subjects assigned to a student's class or academic year.
     */
    public function getFullSubjectsForClass(Student $student): Collection
    {
        if ($student->section && $student->section->subjects()->exists()) {
            return $student->section->subjects()
                ->where('subjects.status', 'active')
                ->orderBy('subjects.display_order', 'asc')
                ->orderBy('subjects.name', 'asc')
                ->get();
        }

        $subjects = collect();
        $class = SchoolClass::find($student->class_id);

        if ($class) {
            $subjects = $class->subjects()
                ->where('subjects.status', 'active')
                ->orderBy('subjects.display_order', 'asc')
                ->orderBy('subjects.name', 'asc')
                ->get();
        }

        if ($subjects->isEmpty() && $student->academic_year_id) {
            $subjects = Subject::where('academic_year_id', $student->academic_year_id)
                ->where('status', 'active')
                ->orderBy('display_order', 'asc')
                ->orderBy('name', 'asc')
                ->get();
        }

        if ($subjects->isEmpty()) {
            $subjects = Subject::where('status', 'active')
                ->orderBy('display_order', 'asc')
                ->orderBy('name', 'asc')
                ->get();
        }

        // If Higher Secondary and student belongs to a stream section, filter by stream
        if ($student->allowsIndividualSubjectAllocation() && $student->section) {
            $sectionName = strtolower(trim((string) $student->section->name));
            $isHumanities = str_contains($sectionName, 'humanities') || str_contains($sectionName, 'arts');
            $isScience = str_contains($sectionName, 'science');

            if ($isHumanities) {
                $subjects = $subjects->filter(fn ($sub) => !preg_match('/\b(biology|bio|physics|chemistry)\b/i', strtolower($sub->name)))->values();
            } elseif ($isScience) {
                $subjects = $subjects->filter(fn ($sub) => !preg_match('/\b(history|political science|civics|education)\b/i', strtolower($sub->name)))->values();
            }
        }

        return $subjects;
    }

    /**
     * Get eligible students for a specific subject within Class + Section + Academic Year.
     * STRICT REQUIREMENT: Order students by ROLL NUMBER ASCENDING.
     * STRICT RULE:
     * - For classes other than 11th & 12th: ALL students in the class/section are eligible (full subjects by default).
     * - For 11th & 12th: only students who have been allocated this subject (or all if no allocations configured yet).
     */
    public function getEligibleStudentsForSubject(
        int $academicYearId,
        int $classId,
        int $sectionId,
        int $subjectId
    ): Collection {
        $class = SchoolClass::find($classId);

        $query = Student::where('academic_year_id', $academicYearId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('status', 'active');

        // If this class is NOT 11th or 12th, ALL students take full subjects by default!
        if (!$class || !$class->allowsIndividualSubjectAllocation()) {
            return $query->orderBy('roll_no', 'asc')->get();
        }

        // For 11th & 12th: check if any student in this class & section has custom allocations
        $hasIndividualAllocations = StudentSubject::whereHas('student', function ($q) use ($academicYearId, $classId, $sectionId) {
            $q->where('academic_year_id', $academicYearId)
              ->where('class_id', $classId)
              ->where('section_id', $sectionId);
        })->exists();

        if ($hasIndividualAllocations) {
            // Only students who have been allocated this specific subject
            $query->whereHas('studentSubjects', function ($q) use ($subjectId) {
                $q->where('subject_id', $subjectId)
                  ->where('status', 'active');
            });
        }

        // STRICT REQUIREMENT: Order students by ROLL NUMBER ASCENDING
        return $query->orderBy('roll_no', 'asc')->get();
    }

    /**
     * Allocate subjects to an individual student.
     * Only permitted for 11th and 12th class students.
     */
    public function allocateSubjects(Student $student, array $subjectIds, bool $isSpecial = true, ?int $userId = null): void
    {
        if (!$student->allowsIndividualSubjectAllocation()) {
            throw new \InvalidArgumentException('Individual subject allocation is only permitted for 11th and 12th class students.');
        }

        DB::transaction(function () use ($student, $subjectIds, $isSpecial, $userId) {
            // Remove existing allocations not in new array
            StudentSubject::where('student_id', $student->id)
                ->whereNotIn('subject_id', $subjectIds)
                ->delete();

            // Insert or update new allocations
            foreach ($subjectIds as $subjectId) {
                StudentSubject::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'subject_id' => $subjectId,
                        'academic_year_id' => $student->academic_year_id,
                    ],
                    [
                        'is_special' => $isSpecial,
                        'status' => 'active',
                    ]
                );
            }

            AuditLogService::log('student_subjects_allocated', $student, [
                'student_id' => $student->student_id,
                'student_name' => $student->name,
                'subject_ids' => $subjectIds,
                'is_special' => $isSpecial,
            ], $userId);
        });
    }
}
