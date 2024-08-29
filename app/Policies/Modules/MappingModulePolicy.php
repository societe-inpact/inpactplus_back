<?php

namespace App\Policies\Modules;

use App\Exceptions\CustomUnauthorizedException;
use App\Models\Misc\User;

class MappingModulePolicy
{
    /**
     * Determine si l'utilisateur peut lire le mapping.
     *
     * @param User $user
     * @return bool
     * @throws CustomUnauthorizedException
     */
    public function read_mapping(User $user)
    {
        if (!$user->hasPermission('read') && !$user->hasPermission('crud') && !$user->hasRole(['inpact'])) {
            throw new CustomUnauthorizedException();
        }
        return true;
    }

    /**
     * Determine si l'utilisateur peut mapper son fichier importé
     *
     * @param User $user
     * @return bool
     * @throws CustomUnauthorizedException
     */
    public function create_mapping(User $user)
    {
        if (!$user->hasPermission('create') && !$user->hasPermission('crud') && !$user->hasRole(['inpact'])) {
            throw new CustomUnauthorizedException();
        }
        return true;
    }

    /**
     * Determine si l'utilisateur peut mettre à jour le mapping.
     *
     * @param User $user
     * @return bool
     * @throws CustomUnauthorizedException
     */
    public function update_mapping(User $user)
    {
        if (!$user->hasPermission('update') && !$user->hasPermission('crud') && !$user->hasRole(['inpact'])) {
            throw new CustomUnauthorizedException();
        }
        return true;
    }

    /**
     * Determine si l'utilisateur peut supprimer le mapping.
     *
     * @param User $user
     * @return bool
     * @throws CustomUnauthorizedException
     */
    public function delete_mapping(User $user)
    {
        if (!$user->hasPermission('delete') && !$user->hasPermission('crud') && !$user->hasRole(['inpact'])) {
            throw new CustomUnauthorizedException();
        }
        return true;
    }
}
