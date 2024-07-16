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
        Schema::create('module_convert', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convert')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('mapping')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('company_folder_id')->constrained('company_folders')->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees', 'user_id')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_convert');
    }
};
