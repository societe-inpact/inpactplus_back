<?php

namespace Database\Seeders;

use App\Models\Misc\InterfaceSoftware;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InterfaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $interfaces = [
            ['name' => 'Marathon', 'interface_mapping_id' => null],
            ['name' => 'RHIS', 'interface_mapping_id' => null],
            ['name' => 'SIRH', 'interface_mapping_id' => null],
        ];

        foreach ($interfaces as $interface){
            InterfaceSoftware::create($interface);
        }
    }
}
