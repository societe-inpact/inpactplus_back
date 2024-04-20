<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $hidden = [
        'id',
        'user_id',
        'laravel_through_key',
    ];
    protected $fillable = [
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function infos()
    {
        return $this->hasOneThrough(EmployeeInfo::class, EmployeeEntity::class, 'employee_informations_id', 'id');
    }

    public function employee_entities()
    {
        return $this->hasOne(EmployeeEntity::class);
    }
    public function companies()
    {
        return $this->belongsToMany(CompanyEntity::class, 'employee_entity', 'company_entity_id');
    }
}
