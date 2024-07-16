<?php

namespace App\Models\Modules;

use App\Models\Misc\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Convert extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'module_convert';

    protected $hidden = ["id", "convert", "mapping", "company_folder_id", "employee_id"];

    protected $fillable = ['convert', 'company_folder_id', 'employee_id'];


    public function permissions()
    {
        return $this->hasOne(Role::class, 'id');
    }
}
