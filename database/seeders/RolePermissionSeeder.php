<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view results',
            'create exams',
            'edit exams',
            'delete exams',
            'manage classes',
            'manage sections',
            'manage subjects',
            'manage students',
            'bulk import students',
            'manage teachers',
            'assign teachers',
            'assign special subjects',
            'view marks',
            'edit marks',
            'unlock submitted marks',
            'verify marks',
            'lock marks',
            'publish results',
            'unpublish results',
            'generate award rolls',
            'download award roll PDFs',
            'view reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Super Admin Role
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::all());

        // Teacher Role
        $teacherRole = Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        $teacherRole->syncPermissions([
            'view marks',
            'edit marks',
        ]);
    }
}
