<?php

namespace App\Policies;

use App\Models\Exam;
use App\Models\User;

class ExamPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Exam $exam): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasPermissionTo('create exams');
    }

    public function update(User $user, Exam $exam): bool
    {
        return $user->hasRole('super-admin') || $user->hasPermissionTo('edit exams');
    }

    public function delete(User $user, Exam $exam): bool
    {
        return $user->hasRole('super-admin') || $user->hasPermissionTo('delete exams');
    }

    public function publish(User $user, Exam $exam): bool
    {
        return $user->hasRole('super-admin') || $user->hasPermissionTo('publish results');
    }
}
