<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Notifications\TeacherWelcomePasswordResetNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TeacherAccountCreationMailTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::clearCache();
        Setting::set('school_name', 'Raza Ul Uloom Islamia Higher Secondary School');
        Setting::set('school_short_name', 'RUIHSS');
        $this->seed(RolePermissionSeeder::class);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super-admin');
    }

    public function test_creating_teacher_sends_welcome_password_reset_email(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->superAdmin)->post(route('admin.teachers.store'), [
            'name' => 'Prof. Farooq Ahmad',
            'email' => 'farooq.teacher@ruihss.edu',
            'phone' => '9876543210',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.teachers.index'));

        $teacher = User::where('email', 'farooq.teacher@ruihss.edu')->first();
        $this->assertNotNull($teacher);
        $this->assertTrue($teacher->hasRole('teacher'));

        Notification::assertSentTo(
            $teacher,
            TeacherWelcomePasswordResetNotification::class,
            function (TeacherWelcomePasswordResetNotification $notification) use ($teacher) {
                $mail = $notification->toMail($teacher);
                $this->assertStringContainsString('RUIHSS', $mail->subject);
                $this->assertStringNotContainsString('[', $mail->subject);
                $this->assertStringNotContainsString(']', $mail->subject);
                $this->assertStringContainsString('farooq.teacher@ruihss.edu', $mail->introLines[1]);
                $this->assertNotEmpty($notification->token);
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

    public function test_teacher_can_set_password_using_token_and_login(): void
    {
        Notification::fake();

        $this->actingAs($this->superAdmin)->post(route('admin.teachers.store'), [
            'name' => 'Zahida Parveen',
            'email' => 'zahida.teacher@ruihss.edu',
            'status' => 'active',
        ]);

        $teacher = User::where('email', 'zahida.teacher@ruihss.edu')->first();

        Notification::assertSentTo(
            $teacher,
            TeacherWelcomePasswordResetNotification::class,
            function (TeacherWelcomePasswordResetNotification $notification) use ($teacher) {
                // Now act as guest (unauthenticated teacher) clicking the link
                $resetResponse = $this->post('/reset-password', [
                    'token' => $notification->token,
                    'email' => $teacher->email,
                    'password' => 'NewSecretPassword123!',
                    'password_confirmation' => 'NewSecretPassword123!',
                ]);

                $resetResponse->assertRedirect(route('login'));

                // Teacher logs in with new password
                $loginResponse = $this->post('/login', [
                    'email' => $teacher->email,
                    'password' => 'NewSecretPassword123!',
                ]);

                $loginResponse->assertRedirect(route('dashboard'));
                $this->assertAuthenticatedAs($teacher);

                return true;
            }
        );
    }
}
