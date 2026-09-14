<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add deleted_at to users (teachers / admins)
        if (!Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->softDeletes()->after('status');
            });
        }

        // Add deleted_at to teacher_assignments
        if (!Schema::hasColumn('teacher_assignments', 'deleted_at')) {
            Schema::table('teacher_assignments', function (Blueprint $table) {
                $table->softDeletes()->after('status');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('teacher_assignments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
