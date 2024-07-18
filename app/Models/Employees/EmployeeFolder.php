<?php

namespace App\Models\Employees;

use App\Models\Companies\Company;
use App\Models\Companies\CompanyFolder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeFolder extends Model
{
    use HasFactory;

    protected $table = 'employee_folder';
    protected $fillable = ['user_id', 'company_folder_id', 'has_access'];


    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'user_id');
    }

    public function folder()
    {
        return $this->belongsTo(CompanyFolder::class, 'company_folder_id');
    }

}
