<?php

namespace App\Policies;

use App\Exceptions\CustomUnauthorizedException;
use App\Models\Misc\User;

class CompanyPolicy
{
    /**
     * Determine si l'utilisateur peut créer une entreprise.
     *
     * @param User $user
     * @return bool
     * @throws CustomUnauthorizedException
     */
    public function create_company(User $user)
    {
        if (!$user->hasPermission('create') && !$user->hasPermission('crud') && !$user->hasRole(['inpact'])) {
            throw new CustomUnauthorizedException();
        }
        return true;
    }

    /**
     * Determine si l'utilisateur peut voir l'entreprise.
     *
     * @param User $user
     * @return bool
     * @throws CustomUnauthorizedException
     */
    public function read_company(User $user)
    {
        if (!$user->hasPermission('read') && !$user->hasPermission('crud') && !$user->hasRole(['inpact'])) {
            throw new CustomUnauthorizedException();
        }
        return true;
    }

    /**
     * Determine si l'utilisateur peut mettre à jour l'entreprise.
     *
     * @param User $user
     * @return bool
     * @throws CustomUnauthorizedException
     */
    public function update_company(User $user)
    {
        if (!$user->hasPermission('update') && !$user->hasPermission('crud') && !$user->hasRole(['inpact'])) {
            throw new CustomUnauthorizedException();
        }
        return true;
    }

    /**
     * Determine si l'utilisateur peut supprimer l'entreprise.
     *
     * @param User $user
     * @return bool
     * @throws CustomUnauthorizedException
     */
    public function delete_company(User $user)
    {
        if (!$user->hasPermission('delete') && !$user->hasPermission('crud') && !$user->hasRole(['inpact'])) {
            throw new CustomUnauthorizedException();
        }
        return true;
    }
}
