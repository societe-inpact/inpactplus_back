<?php

namespace Database\Seeders;

use App\Models\Companies\CompanyFolder;
use App\Models\Companies\CompanyFolderModuleAccess;
use App\Models\Modules\Module;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanyFolderModuleAccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companyFolders = CompanyFolder::all();
        $modules = Module::all();

        foreach ($companyFolders as $companyFolder) {
            foreach ($modules as $module) {
                CompanyFolderModuleAccess::create([
                    'company_folder_id' => $companyFolder->id,
                    'module_id' => $module->id,
                    'has_access' => 1
                ]);
            }
        }
    }
}
