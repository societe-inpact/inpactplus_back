<?php

namespace App\Models\Hours;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hour extends Model
{
    use HasFactory;

    protected $table = "hours";
    public $timestamps = false;
    protected $fillable = [
        "code",
        "label",
    ];
}
