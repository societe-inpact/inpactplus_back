<?php

namespace App\Models\Misc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory;

    protected $table = 'model_has_roles';

    protected $fillable = ['role_id', 'model_type', 'model_id'];
    protected $hidden = ['role_id', 'model_type', 'model_id'];
}
