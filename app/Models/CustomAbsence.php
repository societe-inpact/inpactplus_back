<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomAbsence extends Model
{
    use HasFactory;
    
    public $timestamps = false;

    protected $table = 'custom_absences';
    protected $fillable = ['code', 'label', 'base_calcul', 'therapeutic_part_time'];

    public function mappings()
    {
        return $this->morphMany(Mapping::class, 'output');
    }
}
