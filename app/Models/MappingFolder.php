<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MappingFolder extends Model
{
    use HasFactory;

    protected $table = "mapping_folder";

    protected $fillable = [
        'company_folder_id ',
        'mapping_id '
    ];
}
