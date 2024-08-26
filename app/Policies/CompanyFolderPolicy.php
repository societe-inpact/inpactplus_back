<?php

namespace App\Policies;

use App\Models\Misc\User;

class CompanyFolderPolicy
{
    /**
     * Determine si l'utilisateur peut créer un nouveau dossier.
     *
     * @param User $user
     * @return bool
     */
    public function create_company_folder(User $user)
    {
        return $user->hasPermission('create_company_folder') || $user->hasPermission('crud_company_folder') || $user->hasRole('inpact');
    }

    /**
     * Determine si l'utilisateur peut voir le dossier.
     *
     * @param User $user
     * @return bool
     */
    public function read_company_folder(User $user)
    {
        return $user->hasPermission('read_company_folder') || $user->hasPermission('crud_company_folder') || $user->hasRole('inpact');
    }

    /**
     * Determine si l'utilisateur peut mettre à jour le dossier.
     *
     * @param User $user
     * @return bool
     */
    public function update_company_folder(User $user)
    {
        return $user->hasPermission('update_company_folder')
            || $user->hasPermission('crud_company_folder')
            || $user->hasRole(['inpact, referent']);
    }

    /**
     * Determine si l'utilisateur peut supprimer le dossier.
     *
     * @param User $user
     * @return bool
     */
    public function delete_company_folder(User $user)
    {
        return $user->hasPermission('delete_company_folder') || $user->hasPermission('crud_company_folder') || $user->hasRole('inpact');
    }
}
