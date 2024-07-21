<?php

namespace App\Models\Companies;

use App\Models\Employees\Employee;
use App\Models\Employees\EmployeeFolder;
use App\Models\Modules\CompanyModuleAccess;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return $this->belongsToMany(Module::class, 'company_module_access', 'company_id', 'module_id')
            ->withPivot('has_access');
    }

    public function referent(){
        return $this->belongsTo(Employee::class, 'referent_id');
    }

    public function folders()
    {
        return $this->hasMany(CompanyFolder::class, 'company_id');
    }

    public function employees(){
        return $this->hasManyThrough(Employee::class, EmployeeFolder::class, 'id', 'employee_id');
    }
}
