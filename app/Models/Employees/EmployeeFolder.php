<?php

namespace App\Models\Employees;

use App\Models\Companies\Company;
use App\Models\Companies\CompanyFolder;
use App\Models\Misc\User;
use App\Models\Modules\Module;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeFolder extends Model
{
    use HasFactory;

    protected $table = 'employee_folder';
    public $timestamps = false;
    protected $fillable = ['user_id', 'company_folder_id', 'has_access', 'is_referent'];

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function folder()
    {
        return $this->belongsTo(CompanyFolder::class, 'company_folder_id');
    }

}
