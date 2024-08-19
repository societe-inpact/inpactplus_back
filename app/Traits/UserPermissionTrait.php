<?php

namespace App\Traits;

use Spatie\Permission\Models\Permission;

trait UserPermissionTrait
{
    public function getUserPermissions(){
        return $this->belongsToMany(Permission::class, 'user_module_permissions', 'user_id')
            ->select('name', 'label')
            ->withPivot('company_folder_id')
            ->wherePivot('has_access',  true)
            ->distinct();
    }
}
