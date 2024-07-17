<?php

namespace App\Models\Employees;

use App\Models\Companies\Company;
use App\Models\Companies\CompanyFolder;
use App\Models\Misc\Role;
use App\Models\Misc\User;
use App\Models\Modules\AdminPanel;
use App\Models\Modules\Convert;
use App\Models\Modules\EmployeeManagement;
use App\Models\Modules\History;
use App\Models\Modules\Mapping;
use App\Models\Modules\ModuleAccess;
use App\Models\Modules\Statistic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'employees';
    protected $hidden = ['id', 'user_id', 'laravel_through_key','informations_id'];
    protected $fillable = ['user_id', 'informations_id', 'is_company_referent', 'is_folder_referent'];


    // RELATIONS
    public function user()
    {
        return $this->hasOne(User::class);
    }
}
