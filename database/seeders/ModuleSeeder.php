<?php

namespace Database\Seeders;

use App\Models\Modules\Module;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            ['name' => 'convert', 'label' => 'Mapping/Conversion'],
            ['name' => 'statistics', 'label' => 'Statistiques'],
            ['name' => 'history', 'label' => 'Historique'],
            ['name' => 'employee_management', 'label' => 'Gestion des salariés'],
            ['name' => 'admin_panel', 'label' => 'Panel d\'administration'],
        ];

        foreach ($modules as $module){
            Module::create($module);
        }
    }
}
