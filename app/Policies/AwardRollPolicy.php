<?php

namespace App\Policies;

use App\Models\AwardRoll;
use App\Models\User;

class AwardRollPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasPermissionTo('generate award rolls');
    }

    public function generate(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasPermissionTo('generate award rolls');
    }

    public function download(User $user, ?AwardRoll $awardRoll = null): bool
    {
        return $user->hasRole('super-admin') || $user->hasPermissionTo('download award roll PDFs');
    }
}
