<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariableElement extends Model
{
    use HasFactory;
    // Désactiver les timestamps automatiques
    public $timestamps = false;

    protected $table = "variables_elements";

    protected $fillable = [
        "code",
        "label",
    ];
}
