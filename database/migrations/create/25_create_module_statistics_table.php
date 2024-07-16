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
        Schema::create('modules_access', function (Blueprint $table) {
            $table->id();
            $table->boolean('module_convert');
            $table->boolean('module_employees_management');
            $table->boolean('module_history');
            $table->boolean('module_statistics');
            $table->boolean('module_admin_panel');
            $table->foreignId('company_id')->constrained('company')->cascadeOnDelete();
            $table->foreignId('referent_id')->nullable()->constrained('employees', 'user_id')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules_access');
    }
};
