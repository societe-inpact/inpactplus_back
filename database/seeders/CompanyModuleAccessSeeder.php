<?php

namespace Database\Seeders;

use App\Models\Companies\Company;
use App\Models\Companies\CompanyModuleAccess;
use App\Models\Modules\Module;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanyModuleAccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();
        $modules = Module::all();

        foreach ($companies as $company) {
            foreach ($modules as $module) {
                CompanyModuleAccess::create([
                    'company_id' => $company->id,
                    'module_id' => $module->id,
                    'has_access' => 1
                ]);
            }
        }
    }
}
