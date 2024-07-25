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
    protected $hidden = ['laravel_through_key'];
    protected $fillable = [
        'user_id', 'module_id', 'permission_id', 'company_folder_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }

    public function folder(){
        return $this->belongsTo(CompanyFolder::class);
    }
}
