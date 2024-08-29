<?php

namespace App\Traits;

use Spatie\Permission\Models\Permission;

trait UserPermissionTrait
{
    public function getUserPermissions(){
        $accessibleModuleIds = $this->modules()->pluck('id');

        // Récupérer les permissions pour ces modules
        return $this->belongsToMany(Permission::class, 'user_module_permissions', 'user_id')
            ->select('name', 'label')
            ->withPivot('company_folder_id', 'has_access')
            ->wherePivot('has_access', true)
            ->whereIn('user_module_permissions.module_id', $accessibleModuleIds)
            ->distinct();
    }
}
