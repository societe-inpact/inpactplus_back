<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeEntity extends Model
{
    use HasFactory;

    protected $table = 'employee_entity';
    protected $fillable = ['employee_id', 'company_entity_id', 'employee_informations_id', 'autorization'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function companyEntity()
    {
        return $this->belongsTo(CompanyEntity::class);
    }

    public function employeeInfo()
    {
        return $this->belongsTo(EmployeeInfo::class, 'employee_informations_id');
    }
}
