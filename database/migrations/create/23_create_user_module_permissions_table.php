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
        Schema::create('user_module_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->foreignId('company_folder_id')->constrained('company_folders')->onDelete('cascade');
            $table->timestamps();
        });

        DB::table('user_module_permissions')->insert([
            // John Doe
            [
                'id' => 1,
                'user_id' => 4,
                'module_id' => 1,
                'permission_id' => 4,
                'company_folder_id' => 1,
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 2,
                'user_id' => 4,
                'module_id' => 2,
                'permission_id' => 4,
                'company_folder_id' => 1,
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 3,
                'user_id' => 4,
                'module_id' => 3,
                'permission_id' => 4,
                'company_folder_id' => 1,
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 4,
                'user_id' => 4,
                'module_id' => 4,
                'permission_id' => 4,
                'company_folder_id' => 1,
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 5,
                'user_id' => 4,
                'module_id' => 5,
                'permission_id' => 4,
                'company_folder_id' => 1,
                'created_at' => null,
                'updated_at' => null
            ],
            // Jane Doe
            [
                'id' => 6,
                'user_id' => 5,
                'module_id' => 1,
                'permission_id' => 1,
                'company_folder_id' => 2,
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 7,
                'user_id' => 5,
                'module_id' => 2,
                'permission_id' => 1,
                'company_folder_id' => 2,
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 8,
                'user_id' => 5,
                'module_id' => 3,
                'permission_id' => 1,
                'company_folder_id' => 2,
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 9,
                'user_id' => 5,
                'module_id' => 4,
                'permission_id' => 1,
                'company_folder_id' => 2,
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 10,
                'user_id' => 5,
                'module_id' => 5,
                'permission_id' => 1,
                'company_folder_id' => 2,
                'created_at' => null,
                'updated_at' => null
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_module_permissions');
    }
};
