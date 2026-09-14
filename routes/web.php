<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\AwardRollController;
use App\Http\Controllers\Admin\ClassWiseStatementController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\MarkControlController;
use App\Http\Controllers\Admin\SchoolClassController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentImportController;
use App\Http\Controllers\Admin\StudentMarksSheetController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherAssignmentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Teacher\TeacherDashboardController;
use App\Http\Controllers\Teacher\TeacherMarkEntryController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Application Root (Redirect to Login)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
});


/*
|--------------------------------------------------------------------------
| Institutional Legal & Compliance Pages
|--------------------------------------------------------------------------
*/
Route::view('/privacy-policy', 'legal.privacy-policy')->name('privacy.policy');
Route::view('/terms-and-conditions', 'legal.terms')->name('terms.conditions');
Route::redirect('/terms', '/terms-and-conditions')->name('terms');

/*
|--------------------------------------------------------------------------
| Universal Dashboard Redirector (Protected & Role Dispatched)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    $user = Auth::user();
    if ($user->hasRole('super-admin')) {
        return redirect()->route('admin.dashboard');
    }
    if ($user->hasRole('teacher')) {
        return redirect()->route('teacher.dashboard');
    }
    abort(403, 'Unauthorized institutional access. Your account does not have a registered role.');
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::post('/session/keep-alive', function () {
        return response()->json(['status' => 'alive', 'timestamp' => now()->timestamp]);
    })->name('session.keep-alive');
});

/*
|--------------------------------------------------------------------------
| Protected Direct Admin Navigation Aliases (RBAC: super-admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super-admin'])->prefix('admin')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.dashboard'))->name('admin.root');
    Route::get('/dashboard', fn () => redirect()->route('admin.dashboard'))->name('admin.dashboard.alias');
});

/*
|--------------------------------------------------------------------------
| Protected Direct Faculty Navigation Aliases (RBAC: teacher | super-admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:teacher|super-admin'])->prefix('teacher')->group(function () {
    Route::get('/', fn () => redirect()->route('teacher.dashboard'))->name('teacher.root');
    Route::get('/dashboard', fn () => redirect()->route('teacher.dashboard'))->name('teacher.dashboard.alias');
});

/*
|--------------------------------------------------------------------------
| Profile Management (Protected: Authenticated Users)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Super Admin Result Management Routes (Strict RBAC: super-admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super-admin'])->prefix('admin/results')->name('admin.')->group(function () {
    // Dashboard & Monitoring
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Academic Years (Only used methods: index, create, store, edit, update, toggleActive)
    Route::resource('academic-years', AcademicYearController::class)->except(['show', 'destroy']);
    Route::post('academic-years/{academicYear}/toggle-active', [AcademicYearController::class, 'toggleActive'])
        ->name('academic-years.toggle-active');

    // Exams (Only used methods: index, create, store, edit, update, destroy, updateStatus)
    Route::resource('exams', ExamController::class)->except(['show']);
    Route::patch('exams/{exam}/status', [ExamController::class, 'updateStatus'])->name('exams.status');

    // Classes & Sections
    Route::resource('classes', SchoolClassController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::resource('sections', SectionController::class)->withTrashed(['show', 'destroy']);
    Route::post('sections/{section}/assign-subjects', [SectionController::class, 'assignSubjects'])->name('sections.assign-subjects');

    // Subjects (Only used methods: index, create, store, edit, update, destroy)
    Route::resource('subjects', SubjectController::class)->except(['show']);

    // Students & Allocations (Only used methods: import, template, preview, commit, subjects, index, create, store, edit, update, destroy)
    Route::get('students/import', [StudentImportController::class, 'showImportForm'])->name('students.import.form');
    Route::get('students/import/template', [StudentImportController::class, 'downloadTemplate'])->name('students.import.template');
    Route::post('students/import/preview', [StudentImportController::class, 'preview'])->name('students.import.preview');
    Route::post('students/import/commit', [StudentImportController::class, 'commit'])->name('students.import.commit');

    Route::get('students/{student}/subjects', [StudentController::class, 'subjectAllocation'])->name('students.subjects');
    Route::post('students/{student}/subjects', [StudentController::class, 'saveSubjectAllocation'])->name('students.subjects.save');
    Route::get('students/{student}/marks-sheet', [StudentMarksSheetController::class, 'show'])->name('students.marks-sheet');
    Route::get('students/{student}/marks-sheet/pdf', [StudentMarksSheetController::class, 'downloadPdf'])->name('students.marks-sheet.pdf');
    Route::get('marks-sheets', [StudentMarksSheetController::class, 'index'])->name('marks-sheets.index');
    Route::resource('students', StudentController::class)->except(['show']);

    // Teachers & Assignments
    Route::post('teachers/{teacher}/resend-reset-link', [TeacherController::class, 'resendResetLink'])->name('teachers.resend-reset-link');
    Route::post('teachers/{teacher}/toggle-ban', [TeacherController::class, 'toggleBan'])->name('teachers.toggle-ban');
    Route::resource('teachers', TeacherController::class)->except(['show', 'destroy']);
    Route::resource('assignments', TeacherAssignmentController::class)->only(['index', 'create', 'store', 'destroy']);

    // Marks Status, Verification, Lock & Unlock
    Route::get('marks', [MarkControlController::class, 'index'])->name('marks.index');
    Route::get('exams/{exam}/assignments/{assignment}/marks', [MarkControlController::class, 'show'])->name('marks.show');
    Route::post('marks/verify', [MarkControlController::class, 'verify'])->name('marks.verify');
    Route::post('marks/lock', [MarkControlController::class, 'lock'])->name('marks.lock');
    Route::post('marks/unlock', [MarkControlController::class, 'unlock'])->name('marks.unlock');

    // Award Rolls
    Route::get('award-rolls', [AwardRollController::class, 'index'])->name('award-rolls.index');
    Route::post('award-rolls/generate', [AwardRollController::class, 'generate'])->name('award-rolls.generate');
    Route::post('award-rolls/generate-bulk', [AwardRollController::class, 'generateBulk'])->name('award-rolls.generate-bulk');
    Route::get('award-rolls/{awardRoll}/preview', [AwardRollController::class, 'preview'])->name('award-rolls.preview');
    Route::get('award-rolls/{awardRoll}/download', [AwardRollController::class, 'download'])->name('award-rolls.download');
    Route::get('award-rolls/{awardRoll}/download-excel', [AwardRollController::class, 'downloadExcel'])->name('award-rolls.download-excel');

    // Class Wise Statement (Broadsheet)
    Route::get('class-wise-statement', [ClassWiseStatementController::class, 'index'])->name('class-wise-statement.index');
    Route::get('class-wise-statement/pdf', [ClassWiseStatementController::class, 'exportPdf'])->name('class-wise-statement.pdf');
    Route::get('class-wise-statement/excel', [ClassWiseStatementController::class, 'exportExcel'])->name('class-wise-statement.excel');

    // Audit Logs
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // System Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
});

/*
|--------------------------------------------------------------------------
| Teacher Module Routes (Strict RBAC: teacher | super-admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:teacher|super-admin'])->prefix('teacher/results')->name('teacher.')->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');

    // Mark Entry
    Route::get('assignments/{assignment}/marks', [TeacherMarkEntryController::class, 'entry'])->name('marks.entry');
    Route::post('assignments/{assignment}/marks/draft', [TeacherMarkEntryController::class, 'saveDraft'])->name('marks.draft');
    Route::post('assignments/{assignment}/marks/submit', [TeacherMarkEntryController::class, 'submit'])->name('marks.submit');
});

require __DIR__.'/auth.php';
