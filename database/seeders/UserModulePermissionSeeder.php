<?php

namespace Database\Seeders;

use App\Models\Misc\User;
use App\Models\Misc\UserModuleAccess;
use App\Models\Misc\UserModulePermission;
use App\Models\Modules\Module;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;
use Spatie\Permission\Models\Permission;

class UserModulePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $modules = Module::all();

        foreach ($users as $user) {
            $companyFolder = $user->folders->first();
            if ($companyFolder) {
                foreach ($modules as $module) {
                    UserModulePermission::create([
                        'user_id' => $user->id,
                        'module_id' => $module->id,
                        'permission_id' => 5,
                        'company_folder_id' => $companyFolder->id,
                        'created_at' => Date::now(),
                        'updated_at' => Date::now(),
                    ]);
                }
            }
        }
    }
}
