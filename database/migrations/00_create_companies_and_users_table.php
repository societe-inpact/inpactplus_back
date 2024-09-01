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
        // Création de la table 'companies' sans clé étrangère pour 'referent_id'
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description', 120);
            $table->string('telephone', 10)->nullable();
            $table->string('notes', 255)->nullable()->default('Ajouter une note...');
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('civility', ['Monsieur', 'Madame']);
            $table->string('lastname');
            $table->string('firstname');
            $table->string('telephone', 10)->nullable();

            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->index('company_id');
        });

        // Ajout de la clé étrangère 'referent_id' après la création de la table users
        Schema::table('companies', function (Blueprint $table) {
            $table->foreignId('referent_id')->constrained('users')->cascadeOnDelete();
            $table->index('referent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('companies');
    }
};

