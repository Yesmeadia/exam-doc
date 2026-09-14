<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(): View
    {
        $teachers = User::role('teacher')
            ->withCount('teacherAssignments')
            ->orderBy('name')
            ->paginate(15);

        return view('results.admin.teachers.index', compact('teachers'));
    }

    public function create(): View
    {
        return view('results.admin.teachers.create');
    }

    public function store(StoreTeacherRequest $request): RedirectResponse
    {
        $data = $request->validated();
        // Super admin does not provide password; generate secure placeholder and send setup email
        $data['password'] = Hash::make(Str::random(32));
        $data['status'] = $data['status'] ?? 'active';

        $user = User::create($data);
        $user->assignRole('teacher');

        $emailSent = false;
        $mailError = null;

        try {
            // Automatically trigger password reset / setup link to the teacher's email
            $resetStatus = Password::broker()->sendResetLink(['email' => $user->email]);
            $emailSent = ($resetStatus === Password::RESET_LINK_SENT);
        } catch (\Throwable $e) {
            Log::error("Teacher account password reset mail delivery failed for {$user->email}: " . $e->getMessage());
            $resetStatus = 'failed';
            $mailError = $e->getMessage();
        }

        AuditLogService::log('teacher_created', $user, [
            'name' => $user->name,
            'email' => $user->email,
            'auto_password_setup' => true,
            'email_sent' => $emailSent,
        ], Auth::id());

        AuditLogService::log('password_reset_sent', $user, [
            'email' => $user->email,
            'status' => $resetStatus,
        ], Auth::id());

        if ($emailSent) {
            return redirect()->route('admin.teachers.index')->with(
                'success',
                "Teacher account created successfully. A password setup link has been automatically dispatched to {$user->email}."
            );
        }

        return redirect()->route('admin.teachers.index')->with(
            'warning',
            "Teacher account created successfully, but password setup email could not be sent to {$user->email} (" . ($mailError ?: 'Mail delivery failed') . "). You can resend the setup link at any time from the teacher actions."
        );
    }

    public function edit(User $teacher): View
    {
        return view('results.admin.teachers.edit', compact('teacher'));
    }

    public function update(UpdateTeacherRequest $request, User $teacher): RedirectResponse
    {
        $data = $request->validated();
        $passwordUpdated = false;

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
            $passwordUpdated = true;
        } else {
            unset($data['password']);
        }

        $teacher->update($data);

        AuditLogService::log('teacher_updated', $teacher, [
            'name' => $teacher->name,
            'email' => $teacher->email,
            'password_manually_changed' => $passwordUpdated,
        ], Auth::id());

        if ($passwordUpdated) {
            AuditLogService::log('teacher_password_manually_updated', $teacher, [
                'email' => $teacher->email,
            ], Auth::id());
        }

        return redirect()->route('admin.teachers.index')->with(
            'success',
            'Teacher account updated successfully.' . ($passwordUpdated ? ' New password has been set.' : '')
        );
    }

    /**
     * Resend password reset / setup invitation link to the teacher.
     */
    public function resendResetLink(User $teacher): RedirectResponse
    {
        try {
            $status = Password::broker()->sendResetLink(['email' => $teacher->email]);

            AuditLogService::log('password_reset_resent', $teacher, [
                'email' => $teacher->email,
                'status' => $status,
            ], Auth::id());

            if ($status === Password::RESET_LINK_SENT) {
                return back()->with('success', "Password setup link has been re-sent successfully to {$teacher->email}.");
            }

            return back()->withErrors(['error' => __($status)]);
        } catch (\Throwable $e) {
            Log::error("Resend reset link failed for {$teacher->email}: " . $e->getMessage());
            return back()->withErrors(['error' => "Failed to deliver setup mail to {$teacher->email} (" . $e->getMessage() . "). Please verify your mail server is running."]);
        }
    }

    /**
     * Toggle the banned status of a faculty member.
     */
    public function toggleBan(User $teacher): RedirectResponse
    {
        // Safety guard: prevent banning super-admin or oneself
        if ($teacher->hasRole('super-admin') || $teacher->id === Auth::id()) {
            return back()->withErrors(['error' => 'Super Administrator accounts cannot be banned.']);
        }

        $newStatus = ($teacher->status === 'banned') ? 'active' : 'banned';
        $teacher->update(['status' => $newStatus]);

        AuditLogService::log(
            $newStatus === 'banned' ? 'teacher_banned' : 'teacher_unbanned',
            $teacher,
            [
                'name' => $teacher->name,
                'email' => $teacher->email,
                'status' => $newStatus,
            ],
            Auth::id()
        );

        $msg = $newStatus === 'banned'
            ? "Faculty member {$teacher->name} has been banned. Portal access and mark entry privileges are now suspended."
            : "Faculty member {$teacher->name} has been unbanned. Portal access has been restored.";

        return back()->with('success', $msg);
    }
}
