<?php

namespace App\Models\Companies;

use App\Models\Misc\User;
use App\Models\Modules\Module;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = "companies";
    protected $fillable = [
        "name",
        "description",
        "telephone",
        "notes",
        "referent_id"
    ];

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'company_module_access', 'company_id')->where('has_access', true);
    }

    public function referent()
    {
        return $this->hasOne(User::class, 'id', 'referent_id');
    }

    public function folders()
    {
        return $this->hasMany(CompanyFolder::class, 'company_id');
    }

    public function employees()
    {
        return $this->hasMany(User::class, 'company_id');
    }
}
