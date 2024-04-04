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
        Schema::create('company_entity', function (Blueprint $table) {
            $table->id();
            $table->integer('folder_number');
            $table->string('label', 120);
            $table->string('siret', 14);
            $table->string('siren', 9);
            $table->foreignId('company_id')->constrained('company');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_entity');
    }
};
