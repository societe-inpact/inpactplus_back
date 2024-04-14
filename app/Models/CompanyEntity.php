<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyEntity extends Model
{
    use HasFactory;

    protected $table = 'company_entities';

    protected $fillable = [
        "email",
        "folder_number",
        "folder_name",
        "siret",
        "siren",
    ];

    public function company(){
        return $this->belongsTo(Company::class, 'id', 'company_id');
    }
}
