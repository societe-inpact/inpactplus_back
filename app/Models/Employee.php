<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $hidden = [
        'company_entity_id',
        'user_id',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'employee_code',
        'firstname',
        'lastname',
        'company_entity_id',
        'user_id',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company_entity(){
        return $this->belongsTo(Company_Entity::class, 'company_entity_id', 'company_id');
    }
}
