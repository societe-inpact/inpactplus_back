<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariableElement extends Model
{
    use HasFactory;

    protected $table = "variables_elements";

    protected $fillable = [
        "code",
        "label",
    ];
}
