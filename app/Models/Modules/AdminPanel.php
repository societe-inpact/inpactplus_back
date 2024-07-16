<?php

namespace App\Models\Modules;

use App\Models\Misc\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminPanel extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'module_admin_panel';

    protected $fillable = ['permissions', 'company_folder_id', 'employee_id'];


    public function permissions(){
        return $this->hasOne(Role::class, 'id', 'permissions');
    }
}
