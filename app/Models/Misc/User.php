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
use Illuminate\Support\Facades\Auth;
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
        return $this->belongsToMany(Module::class, 'user_module_access', 'user_id', 'module_id')
            ->withPivot('has_access')
            ->wherePivot('has_access', true);
    }


    public function folders()
    {
        return $this->belongsToMany(CompanyFolder::class, 'employee_folder', 'user_id', 'company_folder_id')->withPivot('id');
    }

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
        $userId = Auth::id();
        return $this->belongsToMany(Permission::class, 'user_module_permissions', 'user_id')
            ->join('user_module_access', function ($join) use ($userId) {
                $join->on('user_module_access.module_id', '=', 'user_module_permissions.module_id')
                    ->where('user_module_access.user_id', $userId);
            })
            ->select('permissions.name', 'permissions.label')
            ->where('user_module_access.has_access', true)
            ->distinct();
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

}
