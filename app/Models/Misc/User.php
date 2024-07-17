<?php

namespace App\Models\Misc;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Companies\Company;
use App\Models\Companies\CompanyFolder;
use App\Models\Employees\Employee;
use App\Models\Employees\EmployeeFolder;
use App\Models\Employees\EmployeeInfo;
use App\Models\Modules\AdminPanel;
use App\Models\Modules\Convert;
use App\Models\Modules\EmployeeManagement;
use App\Models\Modules\History;
use App\Models\Modules\Mapping;
use App\Models\Modules\Statistic;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $table = 'users';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'civility',
        'lastname',
        'email',
        'password',
        'telephone'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'employee',
    ];

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

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

}
