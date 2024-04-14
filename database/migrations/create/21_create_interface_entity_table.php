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
        Schema::create('interface_entity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_entity_id')->constrained('company_entities')->cascadeOnDelete();
            $table->foreignId('interface_entity_id')->constrained('interfaces')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interface_entity');
    }
};
