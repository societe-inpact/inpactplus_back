<?php

namespace Database\Seeders;

use App\Models\Misc\Role;
use App\Models\Misc\User;
use App\Models\Misc\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'inpact', 'guard_name' => 'web', 'created_at' => Date::now(), 'updated_at' => Date::now()],
            ['name' => 'referent', 'guard_name' => 'web', 'created_at' => Date::now(), 'updated_at' => Date::now()],
            ['name' => 'client', 'guard_name' => 'web', 'created_at' => Date::now(), 'updated_at' => Date::now()],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name'], 'guard_name' => $role['guard_name']],
                ['created_at' => $role['created_at'], 'updated_at' => $role['updated_at']]
            );
        }

        $inpactUsers = User::where('email', 'like', '%@inpact.fr')->get();

        foreach ($inpactUsers as $inpactUser) {
            DB::table('model_has_roles')->updateOrInsert(
                [
                    'role_id' => 1,
                    'model_type' => 'App\\Models\\Misc\\User',
                    'model_id' => $inpactUser->id,
                ]
            );
        }
    }
}
