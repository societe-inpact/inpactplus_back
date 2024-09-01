<?php

namespace Database\Seeders;

use App\Models\Misc\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            // Utilisateurs clients référents des entreprises
            ['email' => 'jerome.raquin@vapianofrance.fr', 'password' => Hash::make('jerome.raquin@vapianofrance.fr'), 'civility' => 'Monsieur', 'lastname' => 'Raquin', 'firstname' => 'Jérôme', 'telephone' => '0000000000'],
            ['email' => 'jacques.mignault@mcdonaldsfrance.fr', 'password' => Hash::make('jacques.mignault@mcdonalds.fr'), 'civility' => 'Monsieur', 'lastname' => 'Mignault', 'firstname' => 'Jacques', 'telephone' => '0000000000'],
            ['email' => 'alexandre.simon@burgerkingfrance.fr', 'password' => Hash::make('alexandre.simon@burgerkingfrance.fr'), 'civility' => 'Madame', 'lastname' => 'Simon', 'firstname' => 'Alexandre', 'telephone' => '0000000000'],
            ['email' => 'jc.lavorel@gmail.com', 'password' => Hash::make('jc.lavorel@gmail.com'), 'civility' => 'Monsieur', 'lastname' => 'Lavorel', 'firstname' => 'Jean-Claude', 'telephone' => '0000000000'],
            ['email' => 'emilie.smith@lefevre-guillot.com', 'password' => Hash::make('emilie.smith@lefevre-guillot.com'), 'civility' => 'Madame', 'lastname' => 'Smith', 'firstname' => 'Emilie', 'telephone' => '0505050505'],

            // Utilisateurs Inpact
            ['email' => 'j.podvin@inpact.fr', 'password' => Hash::make('j.podvin@inpact.fr'), 'civility' => 'Monsieur', 'lastname' => 'Podvin', 'firstname' => 'Jonathan', 'telephone' => '0606060606'],
            ['email' => 'a.carteret@inpact.fr', 'password' => Hash::make('a.carteret@inpact.fr'), 'civility' => 'Monsieur', 'lastname' => 'Carteret', 'firstname' => 'Aurélien', 'telephone' => '0707070707'],
            ['email' => 'a.detournay@inpact.fr', 'password' => Hash::make('a.detournay@inpact.fr'), 'civility' => 'Monsieur', 'lastname' => 'Detournay', 'firstname' => 'Adrien', 'telephone' => '0808080808'],
            ['email' => 's.marchant@inpact.fr', 'password' => Hash::make('s.marchant@inpact.fr'), 'civility' => 'Monsieur', 'lastname' => 'Marchant', 'firstname' => 'Serge', 'telephone' => '0909090909'],
            ['email' => 'b.francois@inpact.fr', 'password' => Hash::make('b.francois@inpact.fr'), 'civility' => 'Monsieur', 'lastname' => 'Bizot', 'firstname' => 'François', 'telephone' => '010203040506'],
            ['email' => 'b.karla@inpact.fr', 'password' => Hash::make('b.karla@inpact.fr'), 'civility' => 'Madame', 'lastname' => 'Bizot', 'firstname' => 'Karla', 'telephone' => '0607080910'],
            ['email' => 'f.diop@inpact.fr', 'password' => Hash::make('f.diop@inpact.fr'), 'civility' => 'Madame', 'lastname' => 'Diop', 'firstname' => 'Faty', 'telephone' => '061011121314'],

            // Utilisateurs clients associés à une entreprise mais non référent
            // TODO : A créer
        ];

        // Crée les utilisateurs
        foreach ($users as $user) {
            User::create($user);
        }
    }
}
