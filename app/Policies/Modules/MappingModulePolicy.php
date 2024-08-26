<?php

namespace App\Policies\Modules;

use App\Models\Misc\User;

class MappingModulePolicy
{
    /**
     * Determine si l'utilisateur peut lire le mapping.
     *
     * @param User $user
     * @return bool
     */
    public function read_mapping(User $user)
    {
        return $user->hasPermission('read_mapping') || $user->hasPermission('crud_mapping') || $user->hasRole('inpact');
    }

    /**
     * Determine si l'utilisateur peut mapper son fichier importé
     *
     * @param User $user
     * @return bool
     */
    public function create_mapping(User $user)
    {
        return $user->hasPermission('create_mapping')  || $user->hasPermission('crud_mapping') || $user->hasRole('inpact');
    }

    /**
     * Determine si l'utilisateur peut mettre à jour le mapping.
     *
     * @param User $user
     * @return bool
     */
    public function update_mapping(User $user)
    {
        return $user->hasPermission('update_mapping')  || $user->hasPermission('crud_mapping') || $user->hasRole('inpact');
    }

    /**
     * Determine si l'utilisateur peut supprimer le mapping.
     *
     * @param User $user
     * @return bool
     */
    public function delete_mapping(User $user)
    {
        return $user->hasPermission('delete_mapping')  || $user->hasPermission('crud_mapping') || $user->hasRole('inpact');
    }
}
