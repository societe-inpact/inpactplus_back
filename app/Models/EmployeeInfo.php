<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeInfo extends Model
{
    use HasFactory;

    protected $table = 'employee_infos';
    protected $fillable = ['employee_code', 'social_security_number', 'rib', 'postal_address'];

    public function employee(){
        return $this->belongsTo(Employee::class, 'employee_code', 'employee_code');
    }
}
