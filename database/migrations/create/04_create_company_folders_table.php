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
            $table->string('notes', 255);

            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('interface_id')->constrained('interfaces')->cascadeOnDelete();
            $table->foreignId('referent_id')->nullable()->constrained('users')->cascadeOnDelete();
        });

        DB::table('company_folders')->insert([
            [
                'id' => 1,
                'email' => 'admin@inpact.fr',
                'password' => bcrypt('admin@inpact.fr'), // Utilisez bcrypt pour hacher le mot de passe
                'civility' => 'Monsieur',
                'lastname' => 'Admin',
                'firstname' => 'Inpact',
                'telephone' => '0123456789',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'email' => 'client@inpact.fr',
                'password' => bcrypt('client@inpact.fr'), // Utilisez bcrypt pour hacher le mot de passe
                'civility' => 'Madame',
                'lastname' => 'Client',
                'firstname' => 'Inpact',
                'telephone' => '0987654321',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_entities');
    }
};
