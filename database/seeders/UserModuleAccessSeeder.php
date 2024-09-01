<?php

namespace Database\Seeders;

use App\Models\Misc\User;
use App\Models\Misc\UserModuleAccess;
use App\Models\Modules\Module;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserModuleAccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $modules = Module::all();

        foreach ($users as $user) {
            foreach ($modules as $module) {
                UserModuleAccess::create([
                    'user_id' => $user->id,
                    'module_id' => $module->id,
                    'has_access' => 1
                ]);
            }
        }
    }
}
