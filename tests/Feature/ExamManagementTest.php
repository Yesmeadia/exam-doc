<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $teacher;
    protected AcademicYear $academicYear;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super-admin');

        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('teacher');

        $this->academicYear = AcademicYear::create([
            'name' => '2026-27',
            'is_active' => true,
        ]);
    }

    public function test_super_admin_can_create_exam(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.exams.store'), [
            'academic_year_id' => $this->academicYear->id,
            'exam_name' => 'Midterm Examination 2026',
            'exam_code' => 'MID-2026',
            'status' => 'Active',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-15',
        ]);

        $response->assertRedirect(route('admin.exams.index'));
        $this->assertDatabaseHas('exams', [
            'exam_code' => 'MID-2026',
            'exam_name' => 'Midterm Examination 2026',
        ]);
    }

    public function test_teacher_cannot_create_exam(): void
    {
        $response = $this->actingAs($this->teacher)->post(route('admin.exams.store'), [
            'academic_year_id' => $this->academicYear->id,
            'exam_name' => 'Unauthorized Exam',
            'exam_code' => 'UNAUTH-101',
            'status' => 'Active',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('exams', [
            'exam_code' => 'UNAUTH-101',
        ]);
    }

    public function test_super_admin_can_update_exam_status(): void
    {
        $exam = Exam::create([
            'academic_year_id' => $this->academicYear->id,
            'exam_name' => 'Annual Examination 2026',
            'exam_code' => 'ANNUAL-2026',
            'status' => Exam::STATUS_DRAFT,
        ]);

        $response = $this->actingAs($this->superAdmin)->patch(route('admin.exams.status', $exam), [
            'status' => Exam::STATUS_MARK_ENTRY_OPEN,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('exams', [
            'id' => $exam->id,
            'status' => Exam::STATUS_MARK_ENTRY_OPEN,
        ]);
    }
}
