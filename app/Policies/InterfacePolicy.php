<?php

namespace App\Policies;

use App\Exceptions\CustomUnauthorizedException;
use App\Models\Misc\User;

class InterfacePolicy
{
    /**
     * Determine si l'utilisateur peut voir les interfaces.
     *
     * @param User $user
     * @return bool
     * @throws CustomUnauthorizedException
     */
    public function read_interfaces(User $user)
    {
        if (!$user->hasPermission('read') && !$user->hasPermission('crud') && !$user->hasRole(['inpact'])) {
            throw new CustomUnauthorizedException();
        }
        return true;
    }
}
