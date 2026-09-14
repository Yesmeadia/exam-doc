<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class StudentImportTest extends TestCase
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

        $this->academicYear = AcademicYear::create(['name' => '2026-27', 'is_active' => true]);
        $class = SchoolClass::create(['name' => 'Class 10']);
        Section::create(['class_id' => $class->id, 'name' => 'A']);
    }

    public function test_super_admin_can_download_excel_template(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.students.import.template'));
        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename=student_bulk_import_template.xlsx');
    }

    public function test_it_validates_and_previews_spreadsheet_upload(): void
    {
        // Build a temporary spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['Student ID', 'Student Name', 'Class', 'Section', 'Roll No'],
            ['ST901', 'Test Student 1', 'Class 10', 'A', 50],
        ]);

        $filePath = tempnam(sys_get_temp_dir(), 'test_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $file = new UploadedFile(
            $filePath,
            'students.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($this->superAdmin)->post(route('admin.students.import.preview'), [
            'academic_year_id' => $this->academicYear->id,
            'file' => $file,
        ]);

        $response->assertOk();
        $response->assertSee('ST901');
        $response->assertSee('Test Student 1');
        $response->assertSee('Valid Rows Ready for Import');
    }
}
