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
        Schema::create('mapping', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_folder_id')->constrained('company_folders')->cascadeOnDelete();
            $table->json('data');
            $table->timestamps();
        });

        DB::table('mapping')->insert([
            [
                'id' => 1,
                'company_folder_id' => 1,
                'data' => [],
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapping');
    }
};
