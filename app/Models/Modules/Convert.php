<?php

namespace App\Models\Modules;

use App\Models\Misc\Role;
use App\Models\Misc\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class Convert extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'module_convert';

    protected $hidden = ["id", "convert", "mapping", "company_folder_id", "employee_id"];

    protected $fillable = ['convert', 'company_folder_id', 'employee_id'];


    public function permissions()
    {
        return $this->hasMany(Permission::class, 'id', 'convert');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
