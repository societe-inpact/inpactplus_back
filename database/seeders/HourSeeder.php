<?php

namespace Database\Seeders;

use App\Models\Hours\Hour;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hours = [
            ['code' => 'HS-PHNE', 'label' => 'Pause indemnisee'],
            ['code' => 'HS-PHNE.1', 'label' => 'Pause indemnisee'],
            ['code' => 'HS-HROUTE', 'label' => 'Heures de route'],
            ['code' => 'HS-HEROUTE', 'label' => 'Heures de route'],
            ['code' => 'HS-HMOD1', 'label' => 'Heures normales (modulation)'],
            ['code' => 'HS-PHNE25', 'label' => 'Pause indemnisee 25%'],
            ['code' => 'HS-PHNE25.1', 'label' => 'Pause indemnisee 25%'],
            ['code' => 'HS-PHNE50', 'label' => 'Pause indemnisee 50%'],
            ['code' => 'HS-PHNE50.1', 'label' => 'Pause indemnisee 50%'],
            ['code' => 'HS-PHE', 'label' => 'Heures de pause effectives'],
            ['code' => 'HS-HN', 'label' => 'Heures normales'],
            ['code' => 'HS-HAC15', 'label' => 'Heures avenant contrat 15%'],
            ['code' => 'HS-HAC', 'label' => 'Heures avenant contrat'],
            ['code' => 'HS-HAC12', 'label' => 'Heures avenant contrat 12%'],
            ['code' => 'HS-HAC10', 'label' => 'Heures avenant contrat 10%'],
            ['code' => 'HS-HAC25', 'label' => 'Heures avenant contrat 25%'],
            ['code' => 'HS-HAC05', 'label' => 'Heures avenant contrat 5%'],
            ['code' => 'HS-HAC20', 'label' => 'Heures avenant contrat 20%'],
            ['code' => 'HS-HAC10R', 'label' => 'Heures avenant contrat 10% bonifiees en repos'],
            ['code' => 'HS-HAC07', 'label' => 'Heures avenant contrat 7%'],
            ['code' => 'HS-HAC17', 'label' => 'Heures avenant contrat 17%'],
            ['code' => 'HS-HAC06', 'label' => 'Heures avenant contrat 6%'],
            ['code' => 'HS-HC', 'label' => 'Heures complementaires'],
            ['code' => 'HS-HC-HT', 'label' => 'Heures complementaires non exonerees'],
            ['code' => 'HS-HC-AS', 'label' => 'Heures complementaires/astreinte'],
        ];

        foreach ($hours as $hour){
            Hour::create($hour);
        }

    }
}
