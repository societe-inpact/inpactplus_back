<?php

namespace App\Models\Modules;

use App\Models\Companies\Company;
use App\Models\Companies\CompanyFolder;
use App\Models\Misc\User;
use App\Models\Misc\UserModulePermission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class Module extends Model
{
    use HasFactory;

    protected $table = 'modules';
    public $timestamps = false;
    protected $hidden = ['id', 'laravel_through_key', 'pivot'];
    protected $fillable = [
        'name',
        'label'
    ];

    public function userAccess()
    {
        return $this->belongsToMany(User::class, 'user_module_access', 'module_id', 'user_id')
            ->withPivot('has_access')
            ->wherePivot('has_access', true);
    }

    public function companyAccess()
    {
        return $this->belongsToMany(Company::class, 'company_module_access', 'module_id', 'company_id')
            ->withPivot('has_access')
            ->wherePivot('has_access', true);
    }

    public function companyFolderAccess()
    {
        return $this->belongsToMany(CompanyFolder::class, 'company_folder_module_access', 'module_id', 'company_folder_id')
            ->withPivot('has_access')
            ->wherePivot('has_access', true);
    }

    public function userPermissions()
    {

        return $this->belongsToMany(Permission::class, 'user_module_permissions', 'module_id', 'permission_id')
            ->join('user_module_access', function ($join) {
                $join->on('user_module_access.module_id', '=', 'user_module_permissions.module_id')
                    ->where('user_module_access.user_id', Auth::id());
            })
            ->join('company_folders', 'company_folders.id', '=', 'user_module_permissions.company_folder_id')
            ->whereIn('company_folders.id', function ($query) {
                $query->select('company_folder_id')
                    ->from('user_company_folder')
                    ->where('user_id', Auth::id());
            })
            ->select('permissions.id', 'permissions.name', 'permissions.label')
            ->distinct();
    }

    public function userPermissionsForFolder($folderId): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_module_permissions', 'module_id', 'permission_id')
            ->join('user_module_access', function ($join) {
                $join->on('user_module_access.module_id', '=', 'user_module_permissions.module_id')
                    ->where('user_module_access.user_id', Auth::id());
            })
            ->join('company_folders', 'company_folders.id', '=', 'user_module_permissions.company_folder_id')
            ->where('company_folders.id', $folderId)
            ->select('permissions.id', 'permissions.name', 'permissions.label')
            ->distinct();
    }

}
