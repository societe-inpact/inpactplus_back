<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomHour extends Model
{
    use HasFactory;

    protected $table = "custom_hours";

    protected $fillable = [
        "code",
        "label",
    ];

    public function mappings()
    {
        return $this->morphMany(Mapping::class, 'output');
    }
}

