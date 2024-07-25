<?php

namespace App\Models\Misc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'roles';
    protected $hidden = ['id'];

    protected $fillable = ['name', 'guard_name', 'created_at', 'updated_at'];
}
