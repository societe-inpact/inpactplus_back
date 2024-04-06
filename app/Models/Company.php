<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $table = "company";

    protected $fillable = [
        "name",
        "description",
    ];

    public function company_entity(){
        return $this->hasMany(Company_Entity::class, 'company_entity');
    }
}
