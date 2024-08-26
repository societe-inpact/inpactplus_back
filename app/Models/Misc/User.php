<?php

namespace App\Models\Misc;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Companies\Company;
use App\Models\Companies\CompanyFolder;
use App\Models\Employees\EmployeeFolder;
use App\Models\Modules\Module;
use App\Notifications\ResetPasswordNotification;
use App\Traits\ModuleRetrievingTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, ModuleRetrievingTrait;

    protected $table = 'users';

    public $timestamps = false;

    protected $fillable = ['firstname', 'civility', 'lastname', 'email', 'password', 'telephone'];
    protected $hidden = ['company_id', 'password', 'laravel_through_key', 'pivot'];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function modules()
    {
        $accessibleModules = Module::with('permissions')->whereIn('id', function ($query) {
            $query->select('module_id')
                ->from('user_module_permissions')
                ->where('user_id', $this->id)
                ->where('has_access', 1);
        })->pluck('id');

        $folders = $this->folders()->pluck('company_folders.id');

        $folderModules = Module::with('permissions')->whereIn('id', function ($query) use ($folders) {
            $query->select('module_id')
                ->from('company_folder_module_access')
                ->where('has_access', 1)
                ->whereIn('company_folder_id', $folders);
        })->pluck('id');

        $company = $this->company()->pluck('id');
        $companyModules = Module::with('permissions')->whereIn('id', function ($query) use ($company) {
            $query->select('module_id')
                ->from('company_module_access')
                ->where('has_access', 1)
                ->whereIn('company_id', $company);
        })->pluck('id');

        $accessibleModuleIds = $accessibleModules
            ->intersect($folderModules)
            ->intersect($companyModules);

        return Module::whereIn('id', $accessibleModuleIds)->with('userPermissions')->get();
    }


    public function folders()
    {
        return $this->belongsToMany(CompanyFolder::class, 'employee_folder', 'user_id', 'company_folder_id')->withPivot('id');
    }

    // TODO il faudrait corriger companies par companyFolder
    // Déjà existant au dessus
//    public function companies(){
//       return $this->belongsToMany(CompanyFolder::class, 'employee_folder', 'user_id', 'company_folder_id');
//   }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id')->with('permissions');
    }

    public function hasPermission($permissionName)
    {
        if ($this->permissions->contains('name', $permissionName)) {
            return true;
        }

        // Vérifie les permissions des rôles de l'utilisateur
        foreach ($this->roles as $role) {
            foreach ($role->permissions as $permission) {
                if ($permission->name === $permissionName) {
                    return true;
                }
            }
        }

        return false;
    }

    public function permissions(): BelongsToMany
    {
        $query = $this->belongsToMany(Permission::class, 'user_module_permissions', 'user_id')
            ->select('name', 'label')
            ->distinct();

        // Appliquer des conditions supplémentaires basées sur les dossiers
        // if ($this->folders->isNotEmpty()) {
        //    $query->withPivot('company_folder_id')
        //        ->wherePivot('has_access', true)
        //        ->wherePivot('company_folder_id', '=', $this->folders->first()->id);
        //}

        return $query->distinct();
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

}
