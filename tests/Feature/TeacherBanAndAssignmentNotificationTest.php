<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Setting;
use App\Models\Subject;
use App\Models\User;
use App\Notifications\TeacherAssignmentNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TeacherBanAndAssignmentNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $teacher;
    protected AcademicYear $academicYear;
    protected SchoolClass $schoolClass;
    protected Section $section;
    protected Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::clearCache();
        Setting::set('school_name', 'Raza Ul Uloom Islamia Higher Secondary School');
        Setting::set('school_short_name', 'RUIHSS');
        $this->seed(RolePermissionSeeder::class);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super-admin');

        $this->teacher = User::factory()->create([
            'email' => 'farooq.assignment@ruihss.edu',
            'status' => 'active',
            'password' => bcrypt('ValidPassword123!'),
        ]);
        $this->teacher->assignRole('teacher');

        $this->academicYear = AcademicYear::create([
            'name' => '2026-27',
            'is_active' => true,
        ]);

        $this->schoolClass = SchoolClass::create(['name' => 'Class 10']);
        $this->section = Section::create(['class_id' => $this->schoolClass->id, 'name' => 'A']);
        $this->subject = Subject::create(['name' => 'Mathematics']);
    }

    public function test_assigning_class_and_subject_sends_email_notification(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->superAdmin)->post(route('admin.assignments.store'), [
            'teacher_id' => $this->teacher->id,
            'academic_year_id' => $this->academicYear->id,
            'class_id' => $this->schoolClass->id,
            'section_id' => $this->section->id,
            'subject_id' => $this->subject->id,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.assignments.index'));

        Notification::assertSentTo(
            $this->teacher,
            TeacherAssignmentNotification::class,
            function (TeacherAssignmentNotification $notification) {
                $mail = $notification->toMail($this->teacher);
                $this->assertStringContainsString('Class 10', $mail->subject);
                $this->assertStringContainsString('Mathematics', $mail->subject);
                $this->assertStringNotContainsString('[', $mail->subject);
                $this->assertStringNotContainsString(']', $mail->subject);
                $this->assertStringContainsString('2026-27', $mail->introLines[1]);
                $html = (string) $mail->render();
                $this->assertStringContainsString('logo.svg', $html);
                $this->assertStringContainsString('RUIHSS POONCH', $html);
                $this->assertStringContainsString('please contact the YES INDIA Technical Team', $html);
                $this->assertStringNotContainsString('Best regards,&#10;YES INDIA Technical Team', $html);
                $this->assertStringNotContainsString('Best regards,\nYES INDIA Technical Team', $mail->salutation);
                $this->assertStringNotContainsString('institutional examination office', $html);
                return true;
            }
        );
    }

    public function test_admin_can_ban_and_unban_teacher(): void
    {
        // 1. Admin bans the teacher
        $banResponse = $this->actingAs($this->superAdmin)->post(route('admin.teachers.toggle-ban', $this->teacher));
        $banResponse->assertSessionHas('success');

        $this->teacher->refresh();
        $this->assertEquals('banned', $this->teacher->status);

        // 2. Banned teacher attempts to login -> rejected
        $this->post('/logout');
        $loginResponse = $this->post('/login', [
            'email' => $this->teacher->email,
            'password' => 'ValidPassword123!',
        ]);

        $loginResponse->assertSessionHasErrors('email');
        $this->assertGuest();

        // 3. Admin unbans the teacher
        $unbanResponse = $this->actingAs($this->superAdmin)->post(route('admin.teachers.toggle-ban', $this->teacher));
        $unbanResponse->assertSessionHas('success');

        $this->teacher->refresh();
        $this->assertEquals('active', $this->teacher->status);

        // 4. Unbanned teacher can now login successfully
        $this->post('/logout');
        $reLoginResponse = $this->post('/login', [
            'email' => $this->teacher->email,
            'password' => 'ValidPassword123!',
        ]);

        $reLoginResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->teacher);
    }

    public function test_teacher_delete_route_is_removed(): void
    {
        // Deleting a teacher route is removed (not defined in resource except: ['show', 'destroy'])
        $deleteResponse = $this->actingAs($this->superAdmin)->delete('/admin/results/teachers/' . $this->teacher->id);
        $this->assertTrue(in_array($deleteResponse->getStatusCode(), [404, 405]));

        // Teacher remains in database
        $this->assertDatabaseHas('users', ['id' => $this->teacher->id]);
    }
}
