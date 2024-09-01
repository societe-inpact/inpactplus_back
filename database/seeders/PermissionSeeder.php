<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'create', 'label' => 'Création', 'guard_name' => 'web', 'created_at' => Date::now(), 'updated_at' => Date::now()],
            ['name' => 'read', 'label' => 'Lecture seule', 'guard_name' => 'web', 'created_at' => Date::now(), 'updated_at' => Date::now()],
            ['name' => 'update', 'label' => 'Mise à jour', 'guard_name' => 'web', 'created_at' => Date::now(), 'updated_at' => Date::now()],
            ['name' => 'delete', 'label' => 'Suppression', 'guard_name' => 'web', 'created_at' => Date::now(), 'updated_at' => Date::now()],
            ['name' => 'crud', 'label' => 'Accès complet', 'guard_name' => 'web', 'created_at' => Date::now(), 'updated_at' => Date::now()],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name'], 'guard_name' => $permission['guard_name']],
                ['label' => $permission['label'], 'created_at' => $permission['created_at'], 'updated_at' => $permission['updated_at']]
            );
        }
    }
}
