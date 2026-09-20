<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherAssignmentAndIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $teacherA;
    protected User $teacherB;
    protected AcademicYear $academicYear;
    protected SchoolClass $class;
    protected Section $sectionA;
    protected Section $sectionB;
    protected Subject $math;
    protected Subject $science;
    protected Exam $exam;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super-admin');

        $this->teacherA = User::factory()->create(['name' => 'Teacher A']);
        $this->teacherA->assignRole('teacher');

        $this->teacherB = User::factory()->create(['name' => 'Teacher B']);
        $this->teacherB->assignRole('teacher');

        $this->academicYear = AcademicYear::create(['name' => '2026-27', 'is_active' => true]);
        $this->class = SchoolClass::create(['name' => 'Class 10']);
        $this->sectionA = Section::create(['class_id' => $this->class->id, 'name' => 'A']);
        $this->sectionB = Section::create(['class_id' => $this->class->id, 'name' => 'B']);

        $this->math = Subject::create([
            'name' => 'Mathematics',
            'maximum_marks' => 100,
            'pass_marks' => 33,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $this->science = Subject::create([
            'name' => 'Science',
            'maximum_marks' => 100,
            'pass_marks' => 33,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $this->exam = Exam::create([
            'academic_year_id' => $this->academicYear->id,
            'exam_name' => 'Annual Exam 2026',
            'exam_code' => 'ANNUAL-2026',
            'status' => Exam::STATUS_MARK_ENTRY_OPEN,
        ]);
    }

    public function test_super_admin_can_assign_teacher(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.assignments.store'), [
            'teacher_id' => $this->teacherA->id,
            'academic_year_id' => $this->academicYear->id,
            'class_id' => $this->class->id,
            'section_id' => $this->sectionA->id,
            'subject_id' => $this->math->id,
        ]);

        $response->assertRedirect(route('admin.assignments.index'));
        $this->assertDatabaseHas('teacher_assignments', [
            'teacher_id' => $this->teacherA->id,
            'subject_id' => $this->math->id,
            'section_id' => $this->sectionA->id,
        ]);
    }

    public function test_super_admin_can_assign_teacher_to_multiple_sections(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.assignments.store'), [
            'teacher_id' => $this->teacherA->id,
            'academic_year_id' => $this->academicYear->id,
            'class_id' => $this->class->id,
            'section_ids' => [$this->sectionA->id, $this->sectionB->id],
            'subject_id' => $this->math->id,
        ]);

        $response->assertRedirect(route('admin.assignments.index'));
        $this->assertDatabaseHas('teacher_assignments', [
            'teacher_id' => $this->teacherA->id,
            'subject_id' => $this->math->id,
            'section_id' => $this->sectionA->id,
        ]);
        $this->assertDatabaseHas('teacher_assignments', [
            'teacher_id' => $this->teacherA->id,
            'subject_id' => $this->math->id,
            'section_id' => $this->sectionB->id,
        ]);
    }

    public function test_teacher_can_access_own_assignment_mark_entry(): void
    {
        $assignmentA = TeacherAssignment::create([
            'teacher_id' => $this->teacherA->id,
            'academic_year_id' => $this->academicYear->id,
            'class_id' => $this->class->id,
            'section_id' => $this->sectionA->id,
            'subject_id' => $this->math->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->teacherA)->get(route('teacher.marks.entry', [
            'assignment' => $assignmentA->id,
            'exam_id' => $this->exam->id,
        ]));

        $response->assertOk();
        $response->assertSee('Class 10');
        $response->assertSee('Mathematics');
    }

    public function test_teacher_cannot_access_another_teachers_assignment(): void
    {
        $assignmentB = TeacherAssignment::create([
            'teacher_id' => $this->teacherB->id,
            'academic_year_id' => $this->academicYear->id,
            'class_id' => $this->class->id,
            'section_id' => $this->sectionB->id,
            'subject_id' => $this->science->id,
            'status' => 'active',
        ]);

        // Teacher A tries to view Teacher B's assignment
        $response = $this->actingAs($this->teacherA)->get(route('teacher.marks.entry', [
            'assignment' => $assignmentB->id,
            'exam_id' => $this->exam->id,
        ]));

        $response->assertForbidden();
    }

    public function test_teacher_cannot_post_marks_to_another_teachers_assignment(): void
    {
        $assignmentB = TeacherAssignment::create([
            'teacher_id' => $this->teacherB->id,
            'academic_year_id' => $this->academicYear->id,
            'class_id' => $this->class->id,
            'section_id' => $this->sectionB->id,
            'subject_id' => $this->science->id,
            'status' => 'active',
        ]);

        $student = Student::create([
            'student_id' => 'ST001',
            'name' => 'Test Student',
            'dob' => '2012-01-01',
            'academic_year_id' => $this->academicYear->id,
            'class_id' => $this->class->id,
            'section_id' => $this->sectionB->id,
            'roll_no' => 1,
        ]);

        // Teacher A maliciously attempts to POST marks to Assignment B
        $response = $this->actingAs($this->teacherA)->post(route('teacher.marks.draft', $assignmentB), [
            'exam_id' => $this->exam->id,
            'marks' => [
                $student->id => ['marks' => 99, 'is_absent' => false],
            ],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('marks', [
            'exam_id' => $this->exam->id,
            'student_id' => $student->id,
            'marks' => 99,
        ]);
    }
}
