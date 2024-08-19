<?php

namespace App\Models\Companies;

use App\Models\Employees\EmployeeFolder;
use App\Models\Mapping\Mapping;
use App\Models\Misc\CompanyFolderInterface;
use App\Models\Misc\InterfaceSoftware;
use App\Models\Misc\User;
use App\Models\Modules\CompanyModuleAccess;
use App\Models\Modules\Module;
use App\Traits\ModuleRetrievingTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyFolder extends Model
{
    use HasFactory, ModuleRetrievingTrait;

    public $timestamps = false;

    protected $table = 'company_folders';
    protected $fillable = ["company_id", "referent_id", "folder_number", "folder_name", "notes", "siret", "siren", "interface_id"];

    protected $hidden = [
        'company_id',
        'company',
        'laravel_through_key',
        'interface_id',
        'referent_id'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    //Ajout de companies pour le reprendre dans le get user
    // Déjà présent au dessus
//    public function companies()
//    {
//        return $this->belongsTo(Company::class, 'company_id', 'id');
//    }

    public function interfaces()
    {
        return $this->hasManyThrough(
            InterfaceSoftware::class,
            CompanyFolderInterface::class,
            'company_folder_id',
            'id',
            'id',
            'interface_id'
        );
    }

    public function mappings()
    {
        return $this->belongsTo(Mapping::class, 'id', 'company_folder_id');
    }

    public function employees()
    {
        return $this->belongsToMany(User::class, 'employee_folder', 'company_folder_id');
    }

    public function referent(){
        return $this->hasOne(User::class, 'id', 'referent_id');
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'company_folder_module_access', 'company_folder_id')
            ->where('has_access', true)->whereHas('companyAccess');
    }

}
