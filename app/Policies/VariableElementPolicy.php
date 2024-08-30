<?php

namespace App\Policies;

use App\Exceptions\CustomUnauthorizedException;
use App\Models\Misc\User;

class VariableElementPolicy
{
    /**
     * Determine si l'utilisateur peut lire les éléments variables.
     *
     * @param User $user
     * @return bool
     * @throws CustomUnauthorizedException
     */
    public function read_variables_elements(User $user)
    {
        if (!$user->hasPermission('read') && !$user->hasPermission('crud') && !$user->hasRole(['inpact'])) {
            throw new CustomUnauthorizedException();
        }
        return true;
    }

    /**
     * Determine si l'utilisateur peut créer un élément variable
     *
     * @param User $user
     * @return bool
     * @throws CustomUnauthorizedException
     */
    public function create_variable_element(User $user)
    {
        if (!$user->hasPermission('create') && !$user->hasPermission('crud') && !$user->hasRole(['inpact'])) {
            throw new CustomUnauthorizedException();
        }
        return true;
    }

    /**
     * Determine si l'utilisateur peut mettre à jour un élément variable
     *
     * @param User $user
     * @return bool
     * @throws CustomUnauthorizedException
     */
    public function update_variable_element(User $user)
    {
        if (!$user->hasPermission('update') && !$user->hasPermission('crud') && !$user->hasRole(['inpact'])) {
            throw new CustomUnauthorizedException();
        }
        return true;
    }

    /**
     * Determine si l'utilisateur peut supprimer un élément variable
     *
     * @param User $user
     * @return bool
     * @throws CustomUnauthorizedException
     */
    public function delete_variable_element(User $user)
    {
        if (!$user->hasPermission('delete') && !$user->hasPermission('crud') && !$user->hasRole(['inpact'])) {
            throw new CustomUnauthorizedException();
        }
        return true;
    }
}
