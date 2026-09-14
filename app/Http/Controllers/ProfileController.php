<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        \App\Services\AuditLogService::log('profile_updated', $user, [
            'name' => $user->name,
            'email' => $user->email,
        ], $user->id);

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Safety guard: Prevent sole active super-admin from deleting account
        if ($user->hasRole('super-admin') && \App\Models\User::role('super-admin')->where('status', 'active')->count() <= 1) {
            return back()->withErrors(['error' => 'The sole active Super Administrator account cannot be deleted to prevent locking out the system.']);
        }

        \App\Services\AuditLogService::log('user_account_deleted', $user, [
            'user_id' => $user->id,
            'email'   => $user->email,
            'name'    => $user->name,
        ], $user->id);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
