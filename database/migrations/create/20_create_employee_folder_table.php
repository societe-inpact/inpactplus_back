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
        Schema::create('employee_folder', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_referent');
            $table->boolean('has_access');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_folder_id')->constrained('company_folders')->cascadeOnDelete();
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
