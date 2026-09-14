<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AwardRoll;
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
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AwardRollPdfTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $teacher;
    protected AcademicYear $academicYear;
    protected SchoolClass $class;
    protected Section $section;
    protected Subject $math;
    protected Exam $exam;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        Storage::fake('local');

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

        TeacherAssignment::create([
            'teacher_id' => $this->teacher->id,
            'academic_year_id' => $this->academicYear->id,
            'class_id' => $this->class->id,
            'section_id' => $this->section->id,
            'subject_id' => $this->math->id,
        ]);

        $this->exam = Exam::create([
            'academic_year_id' => $this->academicYear->id,
            'exam_name' => 'Annual Examination 2026',
            'exam_code' => 'ANNUAL-2026',
            'status' => Exam::STATUS_MARK_ENTRY_OPEN,
        ]);

        Student::create([
            'student_id' => 'ST001',
            'name' => 'Abdul Rahman',
            'dob' => '2012-04-10',
            'academic_year_id' => $this->academicYear->id,
            'class_id' => $this->class->id,
            'section_id' => $this->section->id,
            'roll_no' => 1,
            'status' => 'active',
        ]);
    }

    public function test_super_admin_can_download_award_roll_pdf_in_memory(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.award-rolls.generate'), [
            'exam_id' => $this->exam->id,
            'class_id' => $this->class->id,
            'section_id' => $this->section->id,
            'subject_id' => $this->math->id,
            'format' => 'pdf',
        ]);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('attachment;', $response->headers->get('content-disposition'));

        // Server keeps NO report file on disk
        $awardRoll = AwardRoll::where('exam_id', $this->exam->id)
            ->where('class_id', $this->class->id)
            ->where('subject_id', $this->math->id)
            ->first();

        $this->assertNotNull($awardRoll);
        $this->assertEquals(1, $awardRoll->student_count);
        $this->assertEquals(100.0, $awardRoll->max_marks);
        Storage::disk('local')->assertMissing($awardRoll->file_path);
    }

    public function test_super_admin_can_download_award_roll_excel_in_memory(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.award-rolls.generate'), [
            'exam_id' => $this->exam->id,
            'class_id' => $this->class->id,
            'section_id' => $this->section->id,
            'subject_id' => $this->math->id,
            'format' => 'excel',
        ]);

        $response->assertOk();
        $this->assertStringContainsString('spreadsheetml.sheet', $response->headers->get('content-type'));
    }

    public function test_teacher_cannot_generate_official_award_roll(): void
    {
        $response = $this->actingAs($this->teacher)->post(route('admin.award-rolls.generate'), [
            'exam_id' => $this->exam->id,
            'class_id' => $this->class->id,
            'section_id' => $this->section->id,
            'subject_id' => $this->math->id,
        ]);

        $response->assertForbidden();
    }

    public function test_award_rolls_archive_lists_latest_generated_first(): void
    {
        // 1. Create first award roll
        $first = AwardRoll::create([
            'exam_id' => $this->exam->id,
            'class_id' => $this->class->id,
            'section_id' => $this->section->id,
            'subject_id' => $this->math->id,
            'file_path' => 'award_rolls/test1.pdf',
            'file_name' => 'test1.pdf',
            'student_count' => 1,
            'max_marks' => 100,
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ]);

        // 2. Create second subject
        $science = Subject::create([
            'name' => 'Science',
            'maximum_marks' => 100,
            'pass_marks' => 33,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $second = AwardRoll::create([
            'exam_id' => $this->exam->id,
            'class_id' => $this->class->id,
            'section_id' => $this->section->id,
            'subject_id' => $science->id,
            'file_path' => 'award_rolls/test2.pdf',
            'file_name' => 'test2.pdf',
            'student_count' => 1,
            'max_marks' => 100,
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ]);

        // Check index: second should be first
        $response = $this->actingAs($this->superAdmin)->get(route('admin.award-rolls.index'));
        $response->assertOk();

        $rolls = $response->viewData('awardRolls');
        $this->assertEquals($second->id, $rolls->first()->id);

        // Now touch/regenerate first: it should become first in list
        $first->updated_at = now()->addHour();
        $first->save();
        $response2 = $this->actingAs($this->superAdmin)->get(route('admin.award-rolls.index'));
        $rolls2 = $response2->viewData('awardRolls');
        $this->assertEquals($first->id, $rolls2->first()->id);
    }
}
