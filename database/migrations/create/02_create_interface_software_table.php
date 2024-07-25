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
        Schema::create('interface_software', function (Blueprint $table) {
            $table->id();
            $table->integer('colonne_matricule');
            $table->integer('colonne_rubrique');
            $table->integer('colonne_valeur');
            $table->integer('colonne_datedeb')->nullable();
            $table->integer('colonne_datefin')->nullable();
            $table->integer('colonne_hj')->nullable();
            $table->integer('colonne_pourcentagetp')->nullable();
            $table->integer('colonne_periode')->nullable();
            $table->string('type_separateur');
            $table->string('format');
        });
        DB::table('interface_software')->insert([
            [
                'id' => 1,
                'colonne_matricule' => 1,
                'colonne_rubrique' => 2,
                'colonne_valeur' => 3,
                'colonne_datedeb' => null,
                'colonne_datefin' => null,
                'colonne_hj' => null,
                'colonne_pourcentagetp' => null,
                'colonne_periode' => null,
                'type_separateur' => ';',
                'format' => 'csv',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interface_software');
    }
};
