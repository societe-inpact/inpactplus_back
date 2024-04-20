<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $table = "companies";

    protected $fillable = [
        "name",
        "description",
        "referent"
    ];

    public function referent(){
        return $this->hasOne(Employee::class, 'user_id', 'referent');
    }

    public function employee_entities()
    {
        return $this->hasMany(EmployeeEntity::class, 'company_entity_id', 'id');
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_entity', 'company_entity_id', 'employee_id');
    }
}
