<?php

namespace App\Models\VariablesElements;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariableElement extends Model
{
    use HasFactory;

    protected $table = "variables_elements";
    public $timestamps = false;
    protected $fillable = [
        "code",
        "label",
        "company_folder_id"
    ];
}
