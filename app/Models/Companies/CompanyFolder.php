<?php

namespace App\Models\Companies;

use App\Models\Employees\EmployeeFolder;
use App\Models\Mapping\Mapping;
use App\Models\Misc\Software;
use App\Models\Misc\User;
use App\Models\Modules\Module;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyFolder extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'company_folders';
    protected $fillable = ["company_id", "referent_id", "folder_number", "folder_name", "notes", "siret", "siren", "interface_id"];

    protected $hidden = [
        'company_id',
        'company',
        'laravel_through_key',
        'interface_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function software()
    {
        return $this->belongsTo(Software::class, 'interface_id');
    }

    public function mappings()
    {
        return $this->belongsTo(Mapping::class, 'id', 'company_folder_id');
    }

    public function users()
    {
        return $this->hasManyThrough(User::class, EmployeeFolder::class, 'company_folder_id', 'id', 'id', 'user_id')->with('modules');
    }

    public function modules()
    {
        return $this->hasMany(CompanyFolderModuleAccess::class, 'company_folder_id', 'id')
            ->with('module')
            ->select('company_folder_id', 'module_id', 'has_access');
    }
}
