<?php

namespace App\Models\Hours;

use App\Models\Mapping\Mapping;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomHour extends Model
{
    use HasFactory;
    // Désactiver les timestamps automatiques
    public $timestamps = false;

    protected $table = "custom_hours";

    protected $fillable = ["code", "label", "company_folder_id"];

    public function mappings()
    {
        return $this->morphMany(Mapping::class, 'output');
    }
}

