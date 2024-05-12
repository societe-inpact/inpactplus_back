<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Employee extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'employees';
    protected $hidden = ['id', 'user_id', 'laravel_through_key'];
    protected $fillable = ['user_id', 'is_company_referent', 'is_folder_referent'];


    // RELATIONS
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function informations()
    {
        return $this->hasOneThrough(EmployeeInfo::class, EmployeeFolder::class, 'employee_informations_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'referent_id');
    }

    public function folders()
    {
        return $this->belongsToMany(CompanyFolder::class, 'employee_folder')->withPivot('is_referent');
    }

}
