<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRequirementsVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $teacher;
    protected AcademicYear $academicYear;
    protected SchoolClass $schoolClass;
    protected Section $section;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::clearCache();
        Setting::set('school_name', 'RUIHSS POONCH');
        $this->seed(RolePermissionSeeder::class);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super-admin');

        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('teacher');

        $this->academicYear = AcademicYear::create([
            'name' => '2026-27',
            'is_active' => true,
        ]);

        $this->schoolClass = SchoolClass::create(['name' => 'Class 10']);
        $this->section = Section::create(['class_id' => $this->schoolClass->id, 'name' => 'A']);
    }

    /**
     * Requirement: Set login page as 1st page.
     */
    public function test_root_url_redirects_to_login_page(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('login'));
    }

    /**
     * Requirement: Design login page as two-sided.
     */
    public function test_login_page_renders_two_sided_design(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        // Check Left Side branding & features
        $response->assertSee(setting('school_name', 'RUIHSS'));
        $response->assertSee(setting('portal_title', 'Examination & Result Portal'));
        $response->assertSee('Streamlined Academic Evaluation');
        $response->assertSee('Strict Roll Number Order');

        // Check Right Side Form
        $response->assertSee('Sign In to Portal');
        $response->assertDontSeeText('Quick Demo');
        $response->assertDontSee('admin@example.com');
        $response->assertDontSee('teacher1@example.com');
    }

    /**
     * Requirement: Fix the exam create page & create sidebar with navigations.
     */
    public function test_exam_create_page_loads_with_sidebar_and_form(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.exams.create'));

        $response->assertOk();
        $response->assertSee('Create New Examination');
        $response->assertSee('Exam Name');
        $response->assertDontSee('Exam Code');
        $response->assertSee('Academic Year');
        $response->assertSee('Initial Lifecycle Status');

        // Check Sidebar Navigations
        $response->assertSee('Result Management');
        $response->assertSee('Academic Setup');
        $response->assertSee('Examinations');
        $response->assertSee('Classes & Sections', false);
        $response->assertSee('Faculty & Students', false);
        $response->assertSee('Examination & Results', false);
        $response->assertSee('Award Rolls (PDF)');
    }

    /**
     * Requirement: Not need the student DOB (DOB is optional).
     */
    public function test_student_can_be_created_without_dob(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.students.store'), [
            'student_id' => 'ST_NODOB_01',
            'name' => 'Tariq Mahmood',
            'dob' => null, // Omitted DOB
            'academic_year_id' => $this->academicYear->id,
            'class_id' => $this->schoolClass->id,
            'section_id' => $this->section->id,
            'roll_no' => 15,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $this->assertDatabaseHas('students', [
            'student_id' => 'ST_NODOB_01',
            'name' => 'Tariq Mahmood',
            'dob' => null,
            'roll_no' => 15,
        ]);
    }

    /**
     * Requirement: Settings page and dynamic school name.
     */
    public function test_settings_page_loads_and_updates_school_name(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.settings.index'));
        $response->assertOk();
        $response->assertSee('School & System Settings');

        $updateResponse = $this->actingAs($this->superAdmin)->post(route('admin.settings.update'), [
            'school_name' => 'Apex Model Academy',
            'school_short_name' => 'AMA',
            'portal_title' => 'Apex Examination Portal',
            'app_name' => 'Apex ERMS',
            'footer_text' => 'All rights reserved Apex 2026',
        ]);

        $updateResponse->assertRedirect();
        $this->assertEquals('Apex Model Academy', setting('school_name'));
        $this->assertEquals('AMA', setting('school_short_name'));
        $this->assertEquals('Apex Examination Portal', setting('portal_title'));
        $this->assertEquals('Apex ERMS', setting('app_name'));
        $this->assertEquals('All rights reserved Apex 2026', setting('footer_text'));
    }

    /**
     * Requirement: Teacher create requires login email and password clearly.
     */
    public function test_teacher_create_page_displays_portal_login_account_section(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.teachers.create'));
        $response->assertOk();
        $response->assertSee('Faculty login account configuration');
        $response->assertSee('Teacher Email Address (Login Username)');
    }

    /**
     * Requirement: Class & Subject codes removed from UI.
     */
    public function test_class_and_subject_create_pages_do_not_require_code(): void
    {
        // Class index does not ask for numeric code in modal
        $classResponse = $this->actingAs($this->superAdmin)->get(route('admin.classes.index'));
        $classResponse->assertOk();
        $classResponse->assertDontSee('Numeric / Short Code');

        // Subject create does not ask for code
        $subjectResponse = $this->actingAs($this->superAdmin)->get(route('admin.subjects.create'));
        $subjectResponse->assertOk();
        $subjectResponse->assertDontSee('Subject Code');
    }

    /**
     * Requirement: Emojis removed from dashboard headers.
     */
    public function test_dashboard_headers_do_not_contain_wave_or_grad_emojis(): void
    {
        $adminDashboard = $this->actingAs($this->superAdmin)->get(route('admin.dashboard'));
        $adminDashboard->assertOk();
        $adminDashboard->assertDontSee('👋');

        $teacherDashboard = $this->actingAs($this->teacher)->get(route('teacher.dashboard'));
        $teacherDashboard->assertOk();
        $teacherDashboard->assertDontSee('🎓');
    }

    /**
     * Requirement: Date and Time configured in IST (Asia/Kolkata).
     */
    public function test_timezone_is_configured_to_asia_kolkata(): void
    {
        $this->assertEquals('Asia/Kolkata', config('app.timezone'));
        $this->assertEquals('Asia/Kolkata', date_default_timezone_get());
        $this->assertEquals('+05:30', now()->format('P'));
    }

    /**
     * Requirement: Privacy Policy and Terms and Conditions routes and auth footers.
     */
    public function test_privacy_policy_and_terms_routes_and_auth_links(): void
    {
        $privacy = $this->get(route('privacy.policy'));
        $privacy->assertOk();
        $privacy->assertSee('Institutional Privacy Policy');

        $terms = $this->get(route('terms.conditions'));
        $terms->assertOk();
        $terms->assertSee('Terms and Conditions of Use');

        $termsRedirect = $this->get('/terms');
        $termsRedirect->assertRedirect('/terms-and-conditions');

        // Auth pages contain Privacy Policy and Terms & Conditions and not the old footer text
        $login = $this->get(route('login'));
        $login->assertOk();
        $login->assertSee('Privacy Policy');
        $login->assertSeeText('Terms & Conditions');
        $login->assertDontSee('Secure Institutional Access');

        $forgot = $this->get(route('password.request'));
        $forgot->assertOk();
        $forgot->assertSee('Privacy Policy');
        $forgot->assertSeeText('Terms & Conditions');
        $forgot->assertDontSee('Security Audit Enforced');
    }

    /**
     * Requirement: Dashboard header does not contain school short name / brand acronym or header chip.
     */
    public function test_dashboard_header_is_clean_without_brand_acronym_chip(): void
    {
        $adminDashboard = $this->actingAs($this->superAdmin)->get(route('admin.dashboard'));
        $adminDashboard->assertOk();
        // Header chip removed
        $adminDashboard->assertDontSee('<!-- Portal Title Header Chip -->');
    }

    /**
     * Requirement: Strict RBAC Protection for Admin and Teacher Pages.
     */
    public function test_rbac_protection_for_admin_and_teacher_pages(): void
    {
        // 1. Guest access to admin routes is redirected to login
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get(route('admin.dashboard'))->assertRedirect('/login');
        $this->get(route('admin.exams.index'))->assertRedirect('/login');
        $this->get(route('admin.teachers.index'))->assertRedirect('/login');
        $this->get(route('admin.settings.index'))->assertRedirect('/login');

        // 2. Guest access to teacher routes is redirected to login
        $this->get('/teacher')->assertRedirect('/login');
        $this->get('/teacher/dashboard')->assertRedirect('/login');
        $this->get(route('teacher.dashboard'))->assertRedirect('/login');

        // 3. Teacher cannot access any Super Admin pages (403 Forbidden)
        $this->actingAs($this->teacher)->get('/admin')->assertForbidden();
        $this->actingAs($this->teacher)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($this->teacher)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($this->teacher)->get(route('admin.teachers.index'))->assertForbidden();
        $this->actingAs($this->teacher)->get(route('admin.settings.index'))->assertForbidden();
        $this->actingAs($this->teacher)->get(route('admin.audit-logs.index'))->assertForbidden();

        // 4. Super Admin can access all admin routes
        $this->actingAs($this->superAdmin)->get('/admin')->assertRedirect(route('admin.dashboard'));
        $this->actingAs($this->superAdmin)->get('/admin/dashboard')->assertRedirect(route('admin.dashboard'));
        $this->actingAs($this->superAdmin)->get(route('admin.dashboard'))->assertOk();

        // 5. Teacher can access teacher routes
        $this->actingAs($this->teacher)->get('/teacher')->assertRedirect(route('teacher.dashboard'));
        $this->actingAs($this->teacher)->get('/teacher/dashboard')->assertRedirect(route('teacher.dashboard'));
        $this->actingAs($this->teacher)->get(route('teacher.dashboard'))->assertOk();
    }

    /**
     * Requirement: Unused routes are eliminated (404 Not Found or 405 Method Not Allowed).
     */
    public function test_unused_routes_are_removed(): void
    {
        // Removed create and edit routes that do not exist (classes uses modals on index)
        $this->actingAs($this->superAdmin)->get('/admin/results/classes/create')->assertStatus(405);
        $this->actingAs($this->superAdmin)->get('/admin/results/classes/1/edit')->assertNotFound();

        // Removed GET show routes on resources where show views/methods do not exist
        $this->actingAs($this->superAdmin)->get('/admin/results/classes/1')->assertStatus(405);
        $this->actingAs($this->superAdmin)->get('/admin/results/academic-years/1')->assertStatus(405);
        $this->actingAs($this->superAdmin)->get('/admin/results/exams/1')->assertStatus(405);
        $this->actingAs($this->superAdmin)->get('/admin/results/subjects/1')->assertStatus(405);
        $this->actingAs($this->superAdmin)->get('/admin/results/teachers/1')->assertStatus(405);
    }
}

