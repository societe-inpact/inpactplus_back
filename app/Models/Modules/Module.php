<?php

namespace App\Models\Modules;

use App\Models\Companies\Company;
use App\Models\Companies\CompanyFolder;
use App\Models\Misc\User;
use App\Models\Misc\UserModulePermission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class Module extends Model
{
    use HasFactory;

    protected $table = 'modules';


    protected $fillable = [
        'name'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_module_permissions', 'module_id', 'user_id');
    }


    public function permissions()
    {
        return $this->hasMany(UserModulePermission::class, 'module_id');
    }

    public function companyModuleAccess()
    {
        return $this->belongsToMany(Company::class, 'company_module_access', 'module_id', 'company_id')
            ->withPivot('has_access');
    }

    public function companyFolderModuleAccess()
    {
        return $this->belongsToMany(CompanyFolder::class, 'company_folder_module_access', 'module_id', 'company_folder_id')
            ->withPivot('has_access');
    }
}
