<?php

namespace App\Policies;

use App\Models\TeacherAssignment;
use App\Models\User;

class TeacherAssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('teacher');
    }

    public function view(User $user, TeacherAssignment $assignment): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->hasRole('teacher') && $assignment->teacher_id === $user->id && $assignment->status === 'active';
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasPermissionTo('assign teachers');
    }

    public function update(User $user, TeacherAssignment $assignment): bool
    {
        return $user->hasRole('super-admin') || $user->hasPermissionTo('assign teachers');
    }

    public function delete(User $user, TeacherAssignment $assignment): bool
    {
        return $user->hasRole('super-admin') || $user->hasPermissionTo('assign teachers');
    }

    public function submit(User $user, TeacherAssignment $assignment): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->hasRole('teacher') && $assignment->teacher_id === $user->id && $assignment->status === 'active';
    }
}
