<?php

namespace App\Models\Mapping;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MappingFolder extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = "mapping_folder";

    protected $fillable = [
        'company_folder_id',
        'mapping_id'
    ];
}

