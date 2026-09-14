<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        date_default_timezone_set('Asia/Kolkata');
        Config::set('app.timezone', 'Asia/Kolkata');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        date_default_timezone_set('Asia/Kolkata');
        Config::set('app.timezone', 'Asia/Kolkata');

        // ─── Password Policy (OWASP compliant) ───────────────────────────────
        // Applied everywhere Password::defaults() is used:
        //   • Reset password flow (NewPasswordController)
        //   • Profile password update (PasswordController)
        //   • Teacher creation fallback (random 32-char token, bypasses this)
        Password::defaults(function () {
            return Password::min(8)
                ->mixedCase()   // at least one uppercase + one lowercase
                ->numbers()     // at least one digit
                ->symbols()     // at least one special character
                ->uncompromised(); // check against HaveIBeenPwned breach database
        });

        try {
            // Auto-create settings table if not yet created (failsafe)
            if (!Schema::hasTable('settings')) {
                Schema::create('settings', function (Blueprint $table) {
                    $table->id();
                    $table->string('key')->unique();
                    $table->text('value')->nullable();
                    $table->string('group')->default('general');
                    $table->timestamps();
                });
            }

            // Auto-create sessions table if not yet created (failsafe)
            if (!Schema::hasTable('sessions')) {
                Schema::create('sessions', function (Blueprint $table) {
                    $table->string('id')->primary();
                    $table->foreignId('user_id')->nullable()->index();
                    $table->string('ip_address', 45)->nullable();
                    $table->text('user_agent')->nullable();
                    $table->longText('payload');
                    $table->integer('last_activity')->index();
                });
            }

            // Seed default settings if empty
            if (Setting::count() === 0) {
                Setting::set('school_name', 'Rahmaniya VHM Higher Secondary School', 'general');
                Setting::set('school_short_name', 'RUIHSS ERMS', 'general');
                Setting::set('app_name', config('app.name', 'ERMS Results'), 'general');
                Setting::set('portal_title', 'Examination & Result Portal', 'general');
                Setting::set('footer_text', 'Examination Result Management System (ERMS)', 'general');
                Setting::set('disclaimer', 'Official results published by school authority. For discrepancies, contact the examination committee.', 'general');
            }

            // Bind app.name dynamically only if an explicit custom app_name is set in DB
            $appName = Setting::get('app_name');
            if (!empty($appName) && $appName !== 'ERMS Results') {
                Config::set('app.name', $appName);
            }
        } catch (\Throwable $e) {
            // Graceful fallback if database is not reachable
        }
    }
}
