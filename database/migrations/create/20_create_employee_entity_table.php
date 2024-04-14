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
        Schema::create('employee_entity', function (Blueprint $table) {
            $table->id();
            $table->string('autorization');
            $table->foreignId('employee_id')->constrained('employees', 'user_id')->cascadeOnDelete();
            $table->foreignId('company_entity_id')->constrained('company_entities')->cascadeOnDelete();
            $table->foreignId('employee_informations_id')->constrained('employee_infos')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_entity');
    }
};
