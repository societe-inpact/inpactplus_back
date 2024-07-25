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
        Schema::create('company_folder_module_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->foreignId('company_folder_id')->constrained('company_folders')->onDelete('cascade');
            $table->boolean('has_access');
        });

        DB::table('company_folder_module_access')->insert([
            [
                'id' => 1,
                'company_folder_id' => 1,
                'module_id' => 1,
                'has_access' => 1,
            ],
            [
                'id' => 2,
                'company_folder_id' => 1,
                'module_id' => 2,
                'has_access' => 0,
            ],
            [
                'id' => 3,
                'company_folder_id' => 1,
                'module_id' => 3,
                'has_access' => 1,
            ],
            [
                'id' => 4,
                'company_folder_id' => 1,
                'module_id' => 4,
                'has_access' => 0,
            ],
            [
                'id' => 5,
                'company_folder_id' => 1,
                'module_id' => 5,
                'has_access' => 0,
            ],
            [
                'id' => 6,
                'company_folder_id' => 2,
                'module_id' => 1,
                'has_access' => 1,
            ],
            [
                'id' => 7,
                'company_folder_id' => 2,
                'module_id' => 2,
                'has_access' => 0,
            ],
            [
                'id' => 8,
                'company_folder_id' => 2,
                'module_id' => 3,
                'has_access' => 1,
            ],
            [
                'id' => 9,
                'company_folder_id' => 2,
                'module_id' => 4,
                'has_access' => 0,
            ],
            [
                'id' => 10,
                'company_folder_id' => 2,
                'module_id' => 5,
                'has_access' => 0,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_folder_module_access');
    }
};
