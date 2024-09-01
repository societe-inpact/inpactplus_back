<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,

            RoleSeeder::class,
            PermissionSeeder::class,
            RoleHasPermissionSeeder::class,

            CompanySeeder::class,
            CompanyFolderSeeder::class,

            ModuleSeeder::class,

            CompanyModuleAccessSeeder::class,
            CompanyFolderModuleAccessSeeder::class,
            UserModuleAccessSeeder::class,
            UserModulePermissionSeeder::class,

            AbsenceSeeder::class,
            HourSeeder::class,
            InterfaceSeeder::class
        ]);
    }
}
