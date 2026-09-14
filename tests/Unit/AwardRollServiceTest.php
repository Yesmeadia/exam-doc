<?php

namespace Tests\Unit;

use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentSubject;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Services\AwardRollService;
use App\Services\StudentSubjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AwardRollServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AwardRollService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AwardRollService(new StudentSubjectService());
    }

    public function test_award_roll_filters_strictly_for_special_subject_allocations(): void
    {
        $academicYear = AcademicYear::create(['name' => '2026-27', 'is_active' => true]);
        $class12 = SchoolClass::create(['name' => 'Class 12']);
        $section = Section::create(['class_id' => $class12->id, 'name' => 'Science']);

        $bio = Subject::create([
            'name' => 'Biology',
            'maximum_marks' => 100,
            'pass_marks' => 33,
            'academic_year_id' => $academicYear->id,
        ]);

        $cs = Subject::create([
            'name' => 'Computer Science',
            'maximum_marks' => 100,
            'pass_marks' => 33,
            'academic_year_id' => $academicYear->id,
        ]);

        $teacher = User::factory()->create(['name' => 'Mr. Ahmed Khan']);
        TeacherAssignment::create([
            'teacher_id' => $teacher->id,
            'academic_year_id' => $academicYear->id,
            'class_id' => $class12->id,
            'section_id' => $section->id,
            'subject_id' => $bio->id,
        ]);

        // Student 1 enrolled in Biology
        $s1 = Student::create([
            'student_id' => 'ST101',
            'name' => 'Student A (Medical)',
            'dob' => '2008-01-01',
            'academic_year_id' => $academicYear->id,
            'class_id' => $class12->id,
            'section_id' => $section->id,
            'roll_no' => 1,
        ]);
        StudentSubject::create([
            'student_id' => $s1->id,
            'subject_id' => $bio->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'active',
        ]);

        // Student 2 enrolled in Computer Science (NOT Biology)
        $s2 = Student::create([
            'student_id' => 'ST102',
            'name' => 'Student B (CS)',
            'dob' => '2008-01-01',
            'academic_year_id' => $academicYear->id,
            'class_id' => $class12->id,
            'section_id' => $section->id,
            'roll_no' => 2,
        ]);
        StudentSubject::create([
            'student_id' => $s2->id,
            'subject_id' => $cs->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'active',
        ]);

        $exam = Exam::create([
            'academic_year_id' => $academicYear->id,
            'exam_name' => 'Annual Examination 2026',
            'exam_code' => 'ANNUAL-2026',
        ]);

        Mark::create([
            'exam_id' => $exam->id,
            'academic_year_id' => $academicYear->id,
            'student_id' => $s1->id,
            'subject_id' => $bio->id,
            'marks' => 88,
            'status' => Mark::STATUS_VERIFIED,
        ]);

        // Query Award Roll for Biology
        $data = $this->service->getAwardRollData($exam, $class12, $section, $bio);

        // Assert only Student 1 is in the Biology Award Roll
        $this->assertEquals(1, $data['total_students']);
        $this->assertEquals('Mr. Ahmed Khan', $data['teacher_name']);
        $this->assertEquals(100.0, $data['maximum_marks']);
        $this->assertEquals('ST101', $data['students'][0]['student_id']);
        $this->assertEquals('88', $data['students'][0]['marks']);

        // Student 2 must NOT appear
        $studentIds = array_column($data['students'], 'student_id');
        $this->assertNotContains('ST102', $studentIds);
    }
}
