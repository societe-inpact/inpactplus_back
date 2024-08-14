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
    protected $fillable = [
        "name",
        "description",
        "referent_id"
    ];

    public function modules()
    {
        return $this->hasManyThrough(Module::class, CompanyModuleAccess::class, 'company_id', 'id', 'id', 'module_id')
            ->select('modules.id', 'modules.name', 'company_module_access.has_access');
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
        return $this->hasManyThrough(User::class, EmployeeFolder::class, 'company_folder_id', 'id', 'id', 'user_id')
            ->join('company_folders', 'employee_folder.company_folder_id', '=', 'company_folders.id')
            ->select('users.*');
    }
}
