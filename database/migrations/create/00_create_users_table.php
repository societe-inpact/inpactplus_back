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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('civility', ['Monsieur', 'Madame']);
            $table->string('lastname');
            $table->string('firstname');
            $table->string('telephone', 10)->nullable();
        });
        DB::table('users')->insert([
            [
                'id' => 1,
                'email' => 'admin@inpact.fr',
                'password' => bcrypt('admin@inpact.fr'), // Utilisez bcrypt pour hacher le mot de passe
                'civility' => 'Monsieur',
                'lastname' => 'Admin',
                'firstname' => 'Inpact',
                'telephone' => '0123456789',
            ],
            [
                'id' => 2,
                'email' => 'client@inpact.fr',
                'password' => bcrypt('client@inpact.fr'), // Utilisez bcrypt pour hacher le mot de passe
                'civility' => 'Madame',
                'lastname' => 'Client',
                'firstname' => 'Inpact',
                'telephone' => '0987654321',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
