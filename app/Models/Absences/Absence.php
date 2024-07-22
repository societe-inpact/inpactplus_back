<?php

namespace App\Models\Absences;

use App\Models\Mapping\Mapping;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    use HasFactory;

    protected $table = "absences";
    public $timestamps = false;
    protected $fillable = [
        "code",
        "label",
        "base_calcul",
        "therapeutic_part-time"
    ];

    public function mappings()
    {
        return $this->morphMany(Mapping::class, 'output');
    }
}
