<?php

namespace App\Traits;

use App\Models\Companies\CompanyFolderModuleAccess;
use App\Models\Modules\CompanyModuleAccess;
use App\Models\Modules\Module;
use Illuminate\Database\Eloquent\Collection;

trait ModuleRetrievingTrait
{
    /**
     * Récupération des modules accessibles à l'utilisateur en tenant compte des accès de la société et du dossier.
     *
     * @return Collection
     */
    public function getUserModules()
    {
        $accessibleModules = Module::whereIn('id', function ($query) {
            $query->select('module_id')
                ->from('user_module_permissions')
                ->where('user_id', $this->id)
                ->where('has_access', 1);
        })->pluck('id');

        $folders = $this->folders()->pluck('company_folders.id');

        $folderModules = Module::whereIn('id', function ($query) use ($folders) {
            $query->select('module_id')
                ->from('company_folder_module_access')
                ->where('has_access', 1)
                ->whereIn('company_folder_id', $folders);
        })->pluck('id');

        $company = $this->company()->pluck('id');
        $companyModules = Module::whereIn('id', function ($query) use ($company) {
            $query->select('module_id')
                ->from('company_module_access')
                ->where('has_access', 1)
                ->whereIn('company_id', $company);
        })->pluck('id');

        $accessibleModuleIds = $accessibleModules
            ->intersect($folderModules)
            ->intersect($companyModules);

        return Module::whereIn('id', $accessibleModuleIds)->get();

    }


    /**
     * Récupération des modules associés à une société.
     *
     * @return Collection
     */
    public function getCompanyModules()
    {
        return $this->hasManyThrough(Module::class, CompanyModuleAccess::class, 'company_id', 'id', 'id', 'module_id')
            ->select('modules.id', 'modules.name', 'company_module_access.has_access')->get();
    }

    /**
     * Récupération des modules associés à un dossier.
     *
     * @return Collection
     */
    public function getFolderModules()
    {
        return $this->hasManyThrough(Module::class, CompanyFolderModuleAccess::class, 'company_folder_id', 'id', 'id', 'module_id')
            ->where('company_folder_module_access.has_access', true)
            ->select('modules.id', 'modules.name', 'company_folder_module_access.has_access')
            ->get();
    }
}
