<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('custom_absences', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50);
            $table->string('label', 120);
            $table->enum('base_calcul', ['H', 'J']);
            $table->float('therapeutic_part_time')->nullable();

            $table->foreignId('company_folder_id')->constrained('company_folders')->cascadeOnDelete();
            $table->index('company_folder_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_absences');
    }
};
