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
use App\Services\MarkEntryService;
use App\Services\StudentSubjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class MarkEntryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MarkEntryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MarkEntryService(new StudentSubjectService());
    }

    public function test_students_are_ordered_strictly_by_roll_number_ascending(): void
    {
        $academicYear = AcademicYear::create(['name' => '2026-27', 'is_active' => true]);
        $class = SchoolClass::create(['name' => 'Class 10']);
        $section = Section::create(['class_id' => $class->id, 'name' => 'A']);
        $subject = Subject::create([
            'name' => 'Mathematics',
            'maximum_marks' => 100,
            'pass_marks' => 33,
            'academic_year_id' => $academicYear->id,
        ]);

        $teacher = User::factory()->create();
        $assignment = TeacherAssignment::create([
            'teacher_id' => $teacher->id,
            'academic_year_id' => $academicYear->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'subject_id' => $subject->id,
            'status' => 'active',
        ]);

        // Insert students in non-sequential order
        $s3 = Student::create([
            'student_id' => 'ST003',
            'name' => 'Zayd',
            'dob' => '2012-01-01',
            'academic_year_id' => $academicYear->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'roll_no' => 15,
        ]);
        $s1 = Student::create([
            'student_id' => 'ST001',
            'name' => 'Bilal',
            'dob' => '2012-01-01',
            'academic_year_id' => $academicYear->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'roll_no' => 2,
        ]);
        $s2 = Student::create([
            'student_id' => 'ST002',
            'name' => 'Ahmad',
            'dob' => '2012-01-01',
            'academic_year_id' => $academicYear->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'roll_no' => 8,
        ]);

        $exam = Exam::create([
            'academic_year_id' => $academicYear->id,
            'exam_name' => 'Term 1 Exam',
            'exam_code' => 'T1-2026',
        ]);

        $students = $this->service->getStudentsWithMarks($assignment, $exam->id);

        $this->assertCount(3, $students);
        $this->assertEquals(2, $students[0]->roll_no);
        $this->assertEquals(8, $students[1]->roll_no);
        $this->assertEquals(15, $students[2]->roll_no);
    }

    public function test_teacher_can_save_draft_and_submit_marks(): void
    {
        $academicYear = AcademicYear::create(['name' => '2026-27', 'is_active' => true]);
        $class = SchoolClass::create(['name' => 'Class 10']);
        $section = Section::create(['class_id' => $class->id, 'name' => 'A']);
        $subject = Subject::create([
            'name' => 'Mathematics',
            'maximum_marks' => 100,
            'pass_marks' => 33,
            'academic_year_id' => $academicYear->id,
        ]);

        $teacher = User::factory()->create();
        $assignment = TeacherAssignment::create([
            'teacher_id' => $teacher->id,
            'academic_year_id' => $academicYear->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'subject_id' => $subject->id,
        ]);

        $student = Student::create([
            'student_id' => 'ST001',
            'name' => 'Abdul',
            'dob' => '2012-01-01',
            'academic_year_id' => $academicYear->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'roll_no' => 1,
        ]);

        $exam = Exam::create([
            'academic_year_id' => $academicYear->id,
            'exam_name' => 'Term 1 Exam',
            'exam_code' => 'T1-2026',
        ]);

        // 1. Save Draft
        $this->service->saveDraft($assignment, $exam->id, [
            $student->id => ['marks' => 75, 'is_absent' => false, 'remarks' => 'Draft test'],
        ], $teacher->id);

        $mark = Mark::where('exam_id', $exam->id)->where('student_id', $student->id)->first();
        $this->assertNotNull($mark);
        $this->assertEquals(75.0, $mark->marks);
        $this->assertEquals('draft', $mark->status);
        $this->assertNull($mark->submitted_at);

        // 2. Submit Marks
        $this->service->submitMarks($assignment, $exam->id, [
            $student->id => ['marks' => 85, 'is_absent' => false, 'remarks' => 'Final test'],
        ], $teacher->id);

        $mark->refresh();
        $this->assertEquals(85.0, $mark->marks);
        $this->assertEquals('submitted', $mark->status);
        $this->assertNotNull($mark->submitted_at);
    }

    public function test_it_throws_exception_if_mark_exceeds_maximum_marks(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $academicYear = AcademicYear::create(['name' => '2026-27', 'is_active' => true]);
        $class = SchoolClass::create(['name' => 'Class 10']);
        $section = Section::create(['class_id' => $class->id, 'name' => 'A']);
        $subject = Subject::create([
            'name' => 'Mathematics',
            'maximum_marks' => 100,
            'pass_marks' => 33,
            'academic_year_id' => $academicYear->id,
        ]);

        $teacher = User::factory()->create();
        $assignment = TeacherAssignment::create([
            'teacher_id' => $teacher->id,
            'academic_year_id' => $academicYear->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'subject_id' => $subject->id,
        ]);

        $student = Student::create([
            'student_id' => 'ST001',
            'name' => 'Abdul',
            'dob' => '2012-01-01',
            'academic_year_id' => $academicYear->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'roll_no' => 1,
        ]);

        $exam = Exam::create([
            'academic_year_id' => $academicYear->id,
            'exam_name' => 'Term 1 Exam',
            'exam_code' => 'T1-2026',
        ]);

        // Attempting to submit 105 when maximum marks is 100
        $this->service->saveDraft($assignment, $exam->id, [
            $student->id => ['marks' => 105, 'is_absent' => false],
        ], $teacher->id);
    }

    public function test_super_admin_can_unlock_verify_and_lock_marks(): void
    {
        $academicYear = AcademicYear::create(['name' => '2026-27', 'is_active' => true]);
        $class = SchoolClass::create(['name' => 'Class 10']);
        $section = Section::create(['class_id' => $class->id, 'name' => 'A']);
        $subject = Subject::create([
            'name' => 'Mathematics',
            'maximum_marks' => 100,
            'pass_marks' => 33,
            'academic_year_id' => $academicYear->id,
        ]);

        $admin = User::factory()->create();
        $student = Student::create([
            'student_id' => 'ST001',
            'name' => 'Abdul',
            'dob' => '2012-01-01',
            'academic_year_id' => $academicYear->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'roll_no' => 1,
        ]);

        $exam = Exam::create([
            'academic_year_id' => $academicYear->id,
            'exam_name' => 'Term 1 Exam',
            'exam_code' => 'T1-2026',
        ]);

        $mark = Mark::create([
            'exam_id' => $exam->id,
            'academic_year_id' => $academicYear->id,
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'marks' => 88,
            'status' => Mark::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        // 1. Verify Marks
        $this->service->verifyMarks($exam->id, $class->id, $section->id, $subject->id, $admin->id);
        $mark->refresh();
        $this->assertEquals(Mark::STATUS_VERIFIED, $mark->status);
        $this->assertEquals($admin->id, $mark->verified_by);

        // 2. Lock Marks
        $this->service->lockMarks($exam->id, $class->id, $section->id, $subject->id, $admin->id);
        $mark->refresh();
        $this->assertEquals(Mark::STATUS_LOCKED, $mark->status);
        $this->assertEquals($admin->id, $mark->locked_by);

        // 3. Unlock Marks (reverts to draft)
        $this->service->unlockMarks($exam->id, $class->id, $section->id, $subject->id, $admin->id);
        $mark->refresh();
        $this->assertEquals(Mark::STATUS_DRAFT, $mark->status);
        $this->assertEquals($admin->id, $mark->unlocked_by);
        $this->assertNotNull($mark->unlocked_at);
    }
}
