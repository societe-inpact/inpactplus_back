<?php

namespace App\Models\Misc;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Companies\Company;
use App\Models\Companies\CompanyFolder;
use App\Models\Companies\CompanyModuleAccess;
use App\Models\Employees\EmployeeFolder;
use App\Models\Modules\Module;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $table = 'users';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'civility',
        'lastname',
        'email',
        'password',
        'telephone'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'employee',
    ];

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'user_module_permissions', 'user_id', 'module_id');
    }

    public function folders()
    {
        return $this->hasManyThrough(CompanyFolder::class, EmployeeFolder::class, 'user_id', 'id', 'id', 'company_folder_id')->where('has_access', true);
    }

    /**
     * Check if the user has a specific permission for a module if the company has access.
     *
     * @param string $permissionName
     * @param int $moduleId
     * @return bool
     */
    public function companies(){
        return $this->belongsToMany(Company::class, 'employee_folder', 'user_id', 'company_folder_id');
    }

    public function hasPermission(string $permissionName, int $moduleId): bool
    {
        // Check if the company has access to the module
        $hasCompanyAccess = CompanyModuleAccess::where('company_id', $this->company_id)
            ->where('module_id', $moduleId)
            ->where('has_access', true)
            ->exists();

        // If company has access, check if the user has the permission for the module
        if ($hasCompanyAccess) {
            return $this->modulePermissions()
                ->where('permission_id', function ($query) use ($permissionName) {
                    $query->select('id')
                        ->from('permissions')
                        ->where('name', $permissionName)
                        ->limit(1);
                })
                ->where('module_id', $moduleId)
                ->exists();
        }

        return false; // Company doesn't have access, so user doesn't have permission
    }

    public function modulePermissions()
    {
        return $this->hasMany(UserModulePermission::class);
    }

    public function grantAccessToFolder($folderId)
    {
        return $this->accessibleFolders()->updateExistingPivot($folderId, ['has_access' => true]);
    }

    public function revokeAccessToFolder($folderId)
    {
        return $this->accessibleFolders()->updateExistingPivot($folderId, ['has_access' => false]);
    }

    public function grantAccessToFolders(array $folderIds)
    {
        $accessData = array_fill_keys($folderIds, ['has_access' => true]);
        return $this->accessibleFolders()->syncWithoutDetaching($accessData);
    }

    public function revokeAccessFromFolders(array $folderIds)
    {
        return $this->accessibleFolders()->detach($folderIds);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

}
