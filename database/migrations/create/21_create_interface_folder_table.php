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
        Schema::create('interface_folder', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_folder_id')->constrained('company_folders')->cascadeOnDelete();
            $table->foreignId('interface_folder_id')->constrained('interfaces')->cascadeOnDelete();
        });

        DB::table('interface_folder')->insert([
            [
                'id' => 1,
                'company_folder_id' => 1,
                'interface_folder_id' => 1,
            ],
            [
                'id' => 2,
                'company_folder_id' => 2,
                'interface_folder_id' => 2,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interface_entity');
    }
};
