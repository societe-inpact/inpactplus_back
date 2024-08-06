<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_folder', function (Blueprint $table) {
            $table->id();
            $table->boolean('has_access');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_folder_id')->constrained('company_folders')->cascadeOnDelete();
        });

        DB::table('employee_folder')->insert([
            [
                'id' => 1,
                'has_access' => 1,
                'user_id' => 4,
                'company_folder_id' => 1,
            ],
            [
                'id' => 2,
                'has_access' => 1,
                'user_id' => 4,
                'company_folder_id' => 2,
            ],
            [
                'id' => 3,
                'has_access' => 1,
                'user_id' => 5,
                'company_folder_id' => 1,
            ],
            [
                'id' => 4,
                'has_access' => 1,
                'user_id' => 5,
                'company_folder_id' => 2,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_entity');
    }
};
