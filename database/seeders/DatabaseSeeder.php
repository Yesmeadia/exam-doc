<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Run order:
     *   1. RolePermissionSeeder — creates all roles & permissions
     *   2. AdminSeeder          — creates the default super-admin account
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            AdminSeeder::class,
        ]);
    }
}
