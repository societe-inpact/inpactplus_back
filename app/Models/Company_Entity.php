<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company_Entity extends Model
{
    use HasFactory;

    protected $table = 'company_entity';

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
}
