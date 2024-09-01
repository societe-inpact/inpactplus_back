<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleHasPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rolePermissions = [
            ['role_id' => 1, 'permission_id' => 5], // Rôle Inpact a la permission de crud
            ['role_id' => 2, 'permission_id' => 5], // Rôle Referent a la permission de crud
            ['role_id' => 3, 'permission_id' => 2], // Rôle Client a la permission de read
        ];

        foreach ($rolePermissions as $rolePermission) {
            DB::table('role_has_permissions')->updateOrInsert(
                [
                    'role_id' => $rolePermission['role_id'],
                    'permission_id' => $rolePermission['permission_id']
                ]
            );
        }
    }
}
