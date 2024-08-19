<?php

namespace App\Models\Companies;

use App\Models\Employees\Employee;
use App\Models\Employees\EmployeeFolder;
use App\Models\Misc\User;
use App\Models\Modules\CompanyModuleAccess;
use App\Models\Modules\Module;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Company extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = "companies";
    protected $hidden = ['id', 'referent_id'];
    protected $fillable = [
        "name",
        "description",
        "referent_id"
    ];

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'company_module_access', 'company_id')->where('has_access', true);
    }

    public function referent()
    {
        return $this->hasOne(User::class, 'id', 'referent_id');
    }

    public function folders()
    {
        return $this->hasMany(CompanyFolder::class, 'company_id');
    }

    public function employees()
    {
        return $this->hasMany(User::class, 'company_id');
    }
}
