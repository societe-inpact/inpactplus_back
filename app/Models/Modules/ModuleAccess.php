<?php

namespace App\Models\Modules;

use App\Models\Companies\Company;
use App\Models\Misc\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleAccess extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'modules_access';

    protected $hidden = ["id", 'company_id', 'referent_id'];

    protected $fillable = ['module_convert', 'module_employees_management', 'module_history', 'module_statistics', 'module_admin_panel', 'company_id', 'referent_id'];


    // RELATIONS
    public function user()
    {
        return $this->belongsTo(Company::class);
    }
}
