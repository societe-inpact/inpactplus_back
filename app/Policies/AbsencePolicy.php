<?php

namespace App\Policies;

use App\Exceptions\CustomUnauthorizedException;
use App\Models\Misc\User;

class AbsencePolicy
{
    /**
     * Determine si l'utilisateur peut lire les absences.
     *
     * @param User $user
     * @return bool
     * @throws CustomUnauthorizedException
     */
    public function read_absences(User $user)
    {
        if (!$user->hasPermission('read') && !$user->hasPermission('crud') && !$user->hasRole(['inpact'])) {
            throw new CustomUnauthorizedException();
        }
        return true;
    }

    /**
     * Determine si l'utilisateur peut créer une absence
     *
     * @param User $user
     * @return bool
     * @throws CustomUnauthorizedException
     */
    public function create_absence(User $user)
    {
        if (!$user->hasPermission('create') && !$user->hasPermission('crud') && !$user->hasRole(['inpact'])) {
            throw new CustomUnauthorizedException();
        }
        return true;
    }

    /**
     * Determine si l'utilisateur peut mettre à jour une absence.
     *
     * @param User $user
     * @return bool
     * @throws CustomUnauthorizedException
     */
    public function update_absence(User $user)
    {
        if (!$user->hasPermission('update') && !$user->hasPermission('crud') && !$user->hasRole(['inpact'])) {
            throw new CustomUnauthorizedException();
        }
        return true;
    }

    /**
     * Determine si l'utilisateur peut supprimer une absence.
     *
     * @param User $user
     * @return bool
     * @throws CustomUnauthorizedException
     */
    public function delete_absence(User $user)
    {
        if (!$user->hasPermission('delete') && !$user->hasPermission('crud') && !$user->hasRole(['inpact'])) {
            throw new CustomUnauthorizedException();
        }
        return true;
    }
}
