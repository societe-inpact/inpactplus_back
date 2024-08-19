<?php

namespace App\Models\Misc;

use App\Models\Companies\CompanyFolder;
use App\Models\Modules\Module;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class UserModulePermission extends Model
{
    use HasFactory;

    protected $table = 'user_module_permissions';
    protected $hidden = ['id', 'laravel_through_key', 'user_id', 'module_id', 'permission_id', 'has_access', 'pivot'];
    protected $fillable = ['user_id', 'module_id', 'permission_id', 'company_folder_id', 'has_access'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class,'module_id');
    }


    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }

    public function folder()
    {
        return $this->belongsTo(CompanyFolder::class, 'company_folder_id');
    }
}
