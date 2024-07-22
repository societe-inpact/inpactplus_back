<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hours', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50);
            $table->string('label', 255);
        });

        DB::table('hours')->insert([
            ['id' => 1, 'code' => 'HS-PHNE', 'label' => 'Pause indemnisee'],
            ['id' => 2, 'code' => 'HS-PHNE.1', 'label' => 'Pause indemnisee'],
            ['id' => 3, 'code' => 'HS-HROUTE', 'label' => 'Heures de route'],
            ['id' => 4, 'code' => 'HS-HEROUTE', 'label' => 'Heures de route'],
            ['id' => 5, 'code' => 'HS-HMOD1', 'label' => 'Heures normales (modulation)'],
            ['id' => 7, 'code' => 'HS-PHNE25', 'label' => 'Pause indemnisee 25%'],
            ['id' => 8, 'code' => 'HS-PHNE25.1', 'label' => 'Pause indemnisee 25%'],
            ['id' => 9, 'code' => 'HS-PHNE50', 'label' => 'Pause indemnisee 50%'],
            ['id' => 10, 'code' => 'HS-PHNE50.1', 'label' => 'Pause indemnisee 50%'],
            ['id' => 11, 'code' => 'HS-PHE', 'label' => 'Heures de pause effectives'],
            ['id' => 12, 'code' => 'HS-HN', 'label' => 'Heures normales'],
            ['id' => 13, 'code' => 'HS-HAC15', 'label' => 'Heures avenant contrat 15%'],
            ['id' => 14, 'code' => 'HS-HAC', 'label' => 'Heures avenant contrat'],
            ['id' => 15, 'code' => 'HS-HAC12', 'label' => 'Heures avenant contrat 12%'],
            ['id' => 16, 'code' => 'HS-HAC10', 'label' => 'Heures avenant contrat 10%'],
            ['id' => 17, 'code' => 'HS-HAC25', 'label' => 'Heures avenant contrat 25%'],
            ['id' => 18, 'code' => 'HS-HAC05', 'label' => 'Heures avenant contrat 5%'],
            ['id' => 19, 'code' => 'HS-HAC20', 'label' => 'Heures avenant contrat 20%'],
            ['id' => 20, 'code' => 'HS-HAC10R', 'label' => 'Heures avenant contrat 10% bonifiees en repos'],
            ['id' => 21, 'code' => 'HS-HAC07', 'label' => 'Heures avenant contrat 7%'],
            ['id' => 22, 'code' => 'HS-HAC17', 'label' => 'Heures avenant contrat 17%'],
            ['id' => 23, 'code' => 'HS-HAC06', 'label' => 'Heures avenant contrat 6%'],
            ['id' => 24, 'code' => 'HS-HC', 'label' => 'Heures complementaires'],
            ['id' => 25, 'code' => 'HS-HC-HT', 'label' => 'Heures complementaires non exonerees'],
            ['id' => 26, 'code' => 'HS-HC-AS', 'label' => 'Heures complementaires/astreinte'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hours');
    }
};
