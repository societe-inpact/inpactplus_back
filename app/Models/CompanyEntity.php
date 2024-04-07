<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyEntity extends Model
{
    use HasFactory;

    protected $table = 'company_entities';

    protected $fillable = [
        "folder_number",
        "label",
        "siret",
        "siren",
    ];

    protected $hidden = [
        'company_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employees(){
        return $this->belongsToMany(Employee::class, 'employee_entity', 'company_entity_id', 'employee_id');
    }
}
