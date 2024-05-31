<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapping extends Model
{
    use HasFactory;

    protected $table = "mapping";
    protected $fillable = [
        "input_rubrique",
        "name_rubrique",
        "output_rubrique_id",
        "output_type",
        "company_folder_id",
    ];

    public function output()
    {
        return $this->morphTo('output_rubrique', 'output_type', 'output_rubrique_id');
    }
}
