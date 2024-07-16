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

    public function module_convert(){
        return $this->hasOne(Convert::class, 'employee_id', 'user_id');
    }

    public function module_mapping(){
        return $this->hasOne(Mapping::class, 'employee_id', 'user_id');
    }

    public function module_statistics(){
        return $this->hasOne(Statistic::class, 'employee_id', 'user_id');
    }

    public function module_employees_management(){
        return $this->hasOne(EmployeeManagement::class, 'employee_id', 'user_id');
    }

    public function module_history(){
        return $this->hasOne(History::class, 'employee_id', 'user_id');
    }

    public function module_admin_panel(){
        return $this->hasOne(AdminPanel::class, 'employee_id', 'user_id');
    }

    public function informations()
    {
        return $this->hasMany(EmployeeInfo::class, 'id', 'informations_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function folders()
    {
        return $this->hasManyThrough(CompanyFolder::class, EmployeeFolder::class, 'employee_id', 'id', 'user_id', 'company_folder_id')->where('has_access', true);
    }

    public function grantAccessToFolder($folderId)
    {
        return $this->accessibleFolders()->updateExistingPivot($folderId, ['has_access' => true]);
    }

    public function revokeAccessToFolder($folderId)
    {
        return $this->accessibleFolders()->updateExistingPivot($folderId, ['has_access' => false]);
    }

    public function grantAccessToFolders(array $folderIds)
    {
        $accessData = array_fill_keys($folderIds, ['has_access' => true]);
        return $this->accessibleFolders()->syncWithoutDetaching($accessData);
    }

    public function revokeAccessFromFolders(array $folderIds)
    {
        return $this->accessibleFolders()->detach($folderIds);
    }

}
