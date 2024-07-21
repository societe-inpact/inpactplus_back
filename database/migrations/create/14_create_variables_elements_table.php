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
        Schema::create('variables_elements', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50);
            $table->string('label', 255)->nullable();
            $table->foreignId('company_folder_id')->constrained('company_folders')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variables_elements');
    }
};
