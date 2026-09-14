<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Create the default super-admin account.
     *
     * Credentials can be overridden via environment variables:
     *   ADMIN_NAME, ADMIN_EMAIL, ADMIN_PASSWORD
     */
    public function run(): void
    {
        $name     = env('ADMIN_NAME',     'System Administrator');
        $email    = env('ADMIN_EMAIL',    'admin@ruihss.edu');
        $password = env('ADMIN_PASSWORD', 'Admin@1234');

        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'name'     => $name,
                'password' => Hash::make($password),
                'status'   => 'active',
            ]
        );

        // Ensure the super-admin role exists and is assigned
        $role = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $admin->assignRole($role);

        $this->command->info("✅ Admin account ready: {$email}");
    }
}
