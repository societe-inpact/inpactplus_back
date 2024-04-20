<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeEntity extends Model
{
    use HasFactory;

    protected $table = 'employee_entity';
    protected $fillable = ['employee_id', 'company_entity_id', 'employee_informations_id', 'autorization'];


}
