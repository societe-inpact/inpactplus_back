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
    ];

    public function company_entities(){
        return $this->hasMany(CompanyEntity::class);
    }
}
