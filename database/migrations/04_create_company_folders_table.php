<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('company_folders', function (Blueprint $table) {
            $table->id();
            $table->string('folder_number');
            $table->string('folder_name');
            $table->string('siret', 14);
            $table->string('siren', 9);
            $table->string('notes', 255)->nullable()->default('Ajouter une note...');
            $table->string('telephone', 10)->nullable();

            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('referent_id')->nullable()->constrained('users')->cascadeOnDelete();

            $table->index('company_id');
            $table->index('referent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_folders');
    }
};
