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
        Schema::create('company_module_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->boolean('has_access');
        });

        DB::table('company_module_access')->insert([
            [
                'id' => 1,
                'company_id' => 3,
                'module_id' => 1,
                'has_access' => 1,
            ],
            [
                'id' => 2,
                'company_id' => 3,
                'module_id' => 2,
                'has_access' => 0,
            ],
            [
                'id' => 3,
                'company_id' => 3,
                'module_id' => 3,
                'has_access' => 1,
            ],
            [
                'id' => 4,
                'company_id' => 3,
                'module_id' => 4,
                'has_access' => 0,
            ],
            [
                'id' => 5,
                'company_id' => 3,
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
        Schema::dropIfExists('company_module_access');
    }
};
