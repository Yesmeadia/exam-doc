<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarkEntryAndSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $teacher;
    protected AcademicYear $academicYear;
    protected SchoolClass $class;
    protected Section $section;
    protected Subject $math;
    protected Exam $exam;
    protected Student $student;
    protected TeacherAssignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super-admin');

        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('teacher');

        $this->academicYear = AcademicYear::create(['name' => '2026-27', 'is_active' => true]);
        $this->class = SchoolClass::create(['name' => 'Class 10']);
        $this->section = Section::create(['class_id' => $this->class->id, 'name' => 'A']);

        $this->math = Subject::create([
            'name' => 'Mathematics',
            'maximum_marks' => 100,
            'pass_marks' => 33,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $this->student = Student::create([
            'student_id' => 'ST001',
            'name' => 'Abdul Rahman',
            'dob' => '2012-04-10',
            'academic_year_id' => $this->academicYear->id,
            'class_id' => $this->class->id,
            'section_id' => $this->section->id,
            'roll_no' => 1,
            'status' => 'active',
        ]);

        $this->assignment = TeacherAssignment::create([
            'teacher_id' => $this->teacher->id,
            'academic_year_id' => $this->academicYear->id,
            'class_id' => $this->class->id,
            'section_id' => $this->section->id,
            'subject_id' => $this->math->id,
            'status' => 'active',
        ]);

        $this->exam = Exam::create([
            'academic_year_id' => $this->academicYear->id,
            'exam_name' => 'Annual Examination 2026',
            'exam_code' => 'ANNUAL-2026',
            'status' => Exam::STATUS_MARK_ENTRY_OPEN,
        ]);
    }

    public function test_teacher_can_save_draft_and_submit_marks(): void
    {
        // 1. Save Draft
        $response = $this->actingAs($this->teacher)->post(route('teacher.marks.draft', $this->assignment), [
            'exam_id' => $this->exam->id,
            'marks' => [
                $this->student->id => ['marks' => 88, 'is_absent' => false, 'remarks' => 'Good'],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('marks', [
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'marks' => 88,
            'status' => 'draft',
        ]);

        // 2. Submit Marks
        $response = $this->actingAs($this->teacher)->post(route('teacher.marks.submit', $this->assignment), [
            'exam_id' => $this->exam->id,
            'marks' => [
                $this->student->id => ['marks' => 92, 'is_absent' => false, 'remarks' => 'Excellent'],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('marks', [
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'marks' => 92,
            'status' => 'submitted',
        ]);

        // 3. Teacher attempts to edit after submission -> changes should be rejected / ignored
        $this->actingAs($this->teacher)->post(route('teacher.marks.draft', $this->assignment), [
            'exam_id' => $this->exam->id,
            'marks' => [
                $this->student->id => ['marks' => 50, 'is_absent' => false],
            ],
        ]);

        // Still 92, not 50
        $this->assertDatabaseHas('marks', [
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'marks' => 92,
            'status' => 'submitted',
        ]);
    }

    public function test_super_admin_can_unlock_marks_so_teacher_can_edit_again(): void
    {
        // Submit marks initially
        Mark::create([
            'exam_id' => $this->exam->id,
            'academic_year_id' => $this->academicYear->id,
            'student_id' => $this->student->id,
            'subject_id' => $this->math->id,
            'teacher_assignment_id' => $this->assignment->id,
            'marks' => 80,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        // Super Admin unlocks
        $response = $this->actingAs($this->superAdmin)->post(route('admin.marks.unlock'), [
            'exam_id' => $this->exam->id,
            'class_id' => $this->class->id,
            'section_id' => $this->section->id,
            'subject_id' => $this->math->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('marks', [
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'status' => 'draft',
        ]);

        // Teacher can now update marks
        $this->actingAs($this->teacher)->post(route('teacher.marks.draft', $this->assignment), [
            'exam_id' => $this->exam->id,
            'marks' => [
                $this->student->id => ['marks' => 95, 'is_absent' => false],
            ],
        ]);

        $this->assertDatabaseHas('marks', [
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'marks' => 95,
            'status' => 'draft',
        ]);
    }
}
