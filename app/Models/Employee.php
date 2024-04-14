<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $fillable = [
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employeeEntities()
    {
        return $this->hasOne(EmployeeEntity::class);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'employee_entity', 'employee_id', 'company_entity_id');
    }
}
