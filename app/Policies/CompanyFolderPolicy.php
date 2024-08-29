<?php

namespace App\Policies;

use App\Exceptions\CustomUnauthorizedException;
use App\Models\Misc\User;

class CompanyFolderPolicy
{
    /**
     * Determine si l'utilisateur peut créer un nouveau dossier.
     *
     * @param User $user
     * @return bool
     * @throws CustomUnauthorizedException
     */
    public function create_company_folder(User $user)
    {
        if (!$user->hasPermission('create') && !$user->hasPermission('crud') && !$user->hasRole(['inpact'])) {
            throw new CustomUnauthorizedException();
        }
        return true;
    }

    /**
     * Determine si l'utilisateur peut voir le dossier.
     *
     * @param User $user
     * @return bool
     * @throws CustomUnauthorizedException
     */
    public function read_company_folder(User $user)
    {
        if (!$user->hasPermission('read') && !$user->hasPermission('crud') && !$user->hasRole(['inpact'])) {
            throw new CustomUnauthorizedException();
        }
        return true;
    }

    /**
     * Determine si l'utilisateur peut mettre à jour le dossier.
     *
     * @param User $user
     * @return bool
     * @throws CustomUnauthorizedException
     */
    public function update_company_folder(User $user)
    {
        if (!$user->hasPermission('update') && !$user->hasPermission('crud') && !$user->hasRole(['inpact'])) {
            throw new CustomUnauthorizedException();
        }
        return true;
    }

    /**
     * Determine si l'utilisateur peut supprimer le dossier.
     *
     * @param User $user
     * @return bool
     * @throws CustomUnauthorizedException
     */
    public function delete_company_folder(User $user)
    {
        if (!$user->hasPermission('delete') && !$user->hasPermission('crud') && !$user->hasRole(['inpact'])) {
            throw new CustomUnauthorizedException();
        }
        return true;
    }

    /**
     * Determine si l'utilisateur peut créer un nouveau dossier.
     *
     * @param User $user
     * @return bool
     * @throws CustomUnauthorizedException
     */
    public function add_user_to_company_folder(User $user)
    {
        if (!$user->hasPermission('create') && !$user->hasPermission('crud') && !$user->hasRole(['inpact'])) {
            throw new CustomUnauthorizedException();
        }
        return true;
    }

    /**
     * @throws CustomUnauthorizedException
     */
    public function delete_user_from_company_folder(User $user)
    {
        if (!$user->hasPermission('delete') && !$user->hasPermission('crud') && !$user->hasRole(['inpact'])) {
            throw new CustomUnauthorizedException();
        }
        return true;
    }
}
