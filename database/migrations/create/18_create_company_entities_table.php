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
        Schema::create('company_entities', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->integer('folder_number');
            $table->string('folder_name');
            $table->string('siret', 14);
            $table->string('siren', 9);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_entities');
    }
};
