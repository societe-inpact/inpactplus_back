<?php

namespace App\Models\Misc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    use HasFactory;

    protected $table = 'role_has_permissions';
    protected $hidden = ['id'];

    protected $fillable = ['permission_id', 'role_id'];

}
