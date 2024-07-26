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
                'email' => 'j.podvin@inpact.fr',
                'password' => bcrypt('j.podvin@inpact.fr'), // Utilisez bcrypt pour hacher le mot de passe
                'civility' => 'Monsieur',
                'lastname' => 'Podvin',
                'firstname' => 'Jonathan',
                'telephone' => '0123456789',
            ],
            [
                'id' => 2,
                'email' => 'a.carteret@inpact.fr',
                'password' => bcrypt('a.carteret@inpact.fr'), // Utilisez bcrypt pour hacher le mot de passe
                'civility' => 'Monsieur',
                'lastname' => 'Carteret',
                'firstname' => 'Aurélien',
                'telephone' => '0123456789',
            ],
            [
                'id' => 3,
                'email' => 'a.detournay@inpact.fr',
                'password' => bcrypt('a.detournay@inpact.fr'), // Utilisez bcrypt pour hacher le mot de passe
                'civility' => 'Monsieur',
                'lastname' => 'Detournay',
                'firstname' => 'Adrien',
                'telephone' => '0123456789',
            ],
            [
                'id' => 4,
                'email' => 's.marchant@inpact.fr',
                'password' => bcrypt('s.marchant@inpact.fr'), // Utilisez bcrypt pour hacher le mot de passe
                'civility' => 'Monsieur',
                'lastname' => 'Marchant',
                'firstname' => 'Serge',
                'telephone' => '0123456789',
            ],
            [
                'id' => 5,
                'email' => 'k.bizot@inpact.fr',
                'password' => bcrypt('k.bizot@inpact.fr'), // Utilisez bcrypt pour hacher le mot de passe
                'civility' => 'madame',
                'lastname' => 'Bizot',
                'firstname' => 'Karla',
                'telephone' => '0123456789',
            ],
            [
                'id' => 6,
                'email' => 'f.bizot@inpact.fr',
                'password' => bcrypt('f.bizot@inpact.fr'), // Utilisez bcrypt pour hacher le mot de passe
                'civility' => 'Monsieur',
                'lastname' => 'Bizot',
                'firstname' => 'François',
                'telephone' => '0123456789',
            ],
            [
                'id' => 7,
                'email' => 'f.diop@inpact.fr',
                'password' => bcrypt('f.diop@inpact.fr'), // Utilisez bcrypt pour hacher le mot de passe
                'civility' => 'Madame',
                'lastname' => 'Diop',
                'firstname' => 'Faty',
                'telephone' => '0123456789',
            ],
            [
                'id' => 8,
                'email' => 'john.doe@vapiano.fr',
                'password' => bcrypt('john.doe@vapiano.fr'), // Utilisez bcrypt pour hacher le mot de passe
                'civility' => 'Monsieur',
                'lastname' => 'John',
                'firstname' => 'Doe',
                'telephone' => '0987654321',
            ],
            [
                'id' => 9,
                'email' => 'jane.doe@vapiano.fr',
                'password' => bcrypt('jane.doe@vapiano.fr'), // Utilisez bcrypt pour hacher le mot de passe
                'civility' => 'Madame',
                'lastname' => 'Jane',
                'firstname' => 'Doe',
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
