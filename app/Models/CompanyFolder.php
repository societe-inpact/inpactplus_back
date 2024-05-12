<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyFolder extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'company_folders';
    protected $fillable = ["company_id", "folder_number", "folder_name", "siret", "siren"];

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function employees(){
        return $this->belongsToMany(Employee::class, 'employee_folder')->withPivot('is_referent');
    }
}
