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

    public function getEmployees()
    {
        return DB::table('users')
            ->join('employee_folder', 'users.id', '=', 'employee_folder.user_id')
            ->join('company_folders', 'employee_folder.company_folder_id', '=', 'company_folders.id')
            ->where('company_folders.company_id', $this->id)
            ->select('users.*')
            ->get();
    }
}
