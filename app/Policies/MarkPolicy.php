<?php

namespace App\Policies;

use App\Models\TeacherAssignment;
use App\Models\User;

class MarkPolicy
{
    /**
     * Determine whether user can view marks for an assignment.
     */
    public function view(User $user, TeacherAssignment $assignment): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->hasRole('teacher') && $assignment->teacher_id === $user->id && $assignment->status === 'active';
    }

    /**
     * Determine whether user can enter or update marks for an assignment.
     */
    public function update(User $user, TeacherAssignment $assignment): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->hasRole('teacher') && $assignment->teacher_id === $user->id && $assignment->status === 'active';
    }

    /**
     * Determine whether user can submit marks for an assignment.
     */
    public function submit(User $user, TeacherAssignment $assignment): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->hasRole('teacher') && $assignment->teacher_id === $user->id && $assignment->status === 'active';
    }

    /**
     * Determine whether user can unlock marks.
     */
    public function unlock(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasPermissionTo('unlock submitted marks');
    }

    /**
     * Determine whether user can verify marks.
     */
    public function verify(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasPermissionTo('verify marks');
    }

    /**
     * Determine whether user can lock marks.
     */
    public function lock(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasPermissionTo('lock marks');
    }
}
