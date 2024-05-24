<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use function Symfony\Component\Translation\t;

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
