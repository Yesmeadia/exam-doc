<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('students')->update([
            'student_id' => DB::raw('UPPER(TRIM(student_id))'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Conversion to uppercase is irreversible.
    }
};
