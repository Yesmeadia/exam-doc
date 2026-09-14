<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Display the application and school settings management view.
     */
    public function index(): View
    {
        $settings = [
            'school_name' => Setting::get('school_name', 'RAZA UL ULOOM ISLAMIA HIGHER SECONDARY SCHOOL'),
            'school_short_name' => Setting::get('school_short_name', 'RUIHSS ERMS'),
            'portal_title' => Setting::get('portal_title', 'Examination & Result Portal'),
            'app_name' => Setting::get('app_name', config('app.name', 'RUIHSS RMS')),
            'footer_text' => Setting::get('footer_text', 'Result Management System (RMS)'),
        ];

        return view('results.admin.settings.index', compact('settings'));
    }

    /**
     * Update application settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'school_short_name' => ['required', 'string', 'max:60'],
            'portal_title' => ['required', 'string', 'max:150'],
            'app_name' => ['required', 'string', 'max:100'],
            'footer_text' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, trim($value));
        }

        AuditLogService::log('settings_updated', null, $validated, Auth::id());

        return redirect()->route('admin.settings.index')
            ->with('success', 'School & System Settings updated successfully.');
    }
}
