<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeFolder extends Model
{
    use HasFactory;

    protected $table = 'employee_folder';
    protected $fillable = ['employee_id', 'company_folder_id', 'employee_informations_id', 'is_referent'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function folder()
    {
        return $this->belongsTo(CompanyFolder::class, 'company_folder_id');
    }

}
