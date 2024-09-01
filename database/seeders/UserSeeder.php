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
            // Utilisateurs référents des entreprises

            /* USER 0 - ID:1 */
            ['email' => 'jerome.raquin@vapianofrance.fr', 'password' => Hash::make('jerome.raquin@vapianofrance.fr'), 'civility' => 'Monsieur', 'lastname' => 'Raquin', 'firstname' => 'Jérôme', 'telephone' => '0000000000'],
            /* USER 1 - ID:2 */
            ['email' => 'nicolas.garnier@groupe-garnier.fr', 'password' => Hash::make('nicolas.garnier@groupe-garnier.fr'), 'civility' => 'Monsieur', 'lastname' => 'Garnier', 'firstname' => 'Nicolas', 'telephone' => '0000000000'],
            /* USER 2 - ID:3 */
            ['email' => 'alexandre.simon@burgerkingfrance.fr', 'password' => Hash::make('alexandre.simon@burgerkingfrance.fr'), 'civility' => 'Madame', 'lastname' => 'Simon', 'firstname' => 'Alexandre', 'telephone' => '0000000000'],
            /* USER 3 - ID:4 */
            ['email' => 'jean.claude@lavorelhotels.com', 'password' => Hash::make('jean.claude@lavorelhotels.com'), 'civility' => 'Monsieur', 'lastname' => 'Lavorel', 'firstname' => 'Jean-Claude', 'telephone' => '0000000000'],
            /* USER 4 - ID:5 */
            ['email' => 'jacques.coueffe@groupe-gsdi.com', 'password' => Hash::make('jacques.coueffe@groupe-gsdi.com'), 'civility' => 'Monsieur', 'lastname' => 'Coueffe', 'firstname' => 'Jacques', 'telephone' => '0000000000'],

            // ---------------------------------------------------------------------------------------------------------------------------------------------------------------- //

            // Utilisateurs Inpact

            /* USER 5 - ID:6 */
            ['email' => 'j.podvin@inpact.fr', 'password' => Hash::make('j.podvin@inpact.fr'), 'civility' => 'Monsieur', 'lastname' => 'Podvin', 'firstname' => 'Jonathan', 'telephone' => '0101010101'],
            /* USER 6 - ID:7 */
            ['email' => 'a.carteret@inpact.fr', 'password' => Hash::make('a.carteret@inpact.fr'), 'civility' => 'Monsieur', 'lastname' => 'Carteret', 'firstname' => 'Aurélien', 'telephone' => '0101010101'],
            /* USER 7- ID:8 */
            ['email' => 'a.detournay@inpact.fr', 'password' => Hash::make('a.detournay@inpact.fr'), 'civility' => 'Monsieur', 'lastname' => 'Detournay', 'firstname' => 'Adrien', 'telephone' => '0101010101'],
            /* USER 8 - ID:9 */
            ['email' => 's.marchant@inpact.fr', 'password' => Hash::make('s.marchant@inpact.fr'), 'civility' => 'Monsieur', 'lastname' => 'Marchant', 'firstname' => 'Serge', 'telephone' => '0101010101'],
            /* USER 9 - ID:10 */
            ['email' => 'b.francois@inpact.fr', 'password' => Hash::make('b.francois@inpact.fr'), 'civility' => 'Monsieur', 'lastname' => 'Bizot', 'firstname' => 'François', 'telephone' => '0101010101'],
            /* USER 10 - ID:11 */
            ['email' => 'b.karla@inpact.fr', 'password' => Hash::make('b.karla@inpact.fr'), 'civility' => 'Madame', 'lastname' => 'Bizot', 'firstname' => 'Karla', 'telephone' => '0101010101'],
            /* USER 11 - ID:12 */
            ['email' => 'f.diop@inpact.fr', 'password' => Hash::make('f.diop@inpact.fr'), 'civility' => 'Madame', 'lastname' => 'Diop', 'firstname' => 'Faty', 'telephone' => '0101010101'],

            // ---------------------------------------------------------------------------------------------------------------------------------------------------------------- //

            // Utilisateurs référents d'un dossier d'entreprise

            // ---------------------------------------------------------------- VAPIANO ---------------------------------------------------------------- //
            /* USER 12 - ID:13 */
            ['email' => 'karim.kawkab@vapianofrance.fr', 'password' => Hash::make('karim.kawkab@vapianofrance.fr'), 'civility' => 'Monsieur', 'lastname' => 'Kawkab', 'firstname' => 'Karim', 'telephone' => '0622013048'],
            /* USER 13 - ID:14 */
            ['email' => 'amine.kouhil@vapianofrance.fr', 'password' => Hash::make('amine.kouhil@vapianofrance.fr'), 'civility' => 'Monsieur', 'lastname' => 'Kouhil', 'firstname' => 'Amine', 'telephone' => '0751092929'],
            /* USER 14 - ID:15 */
            ['email' => 'thierry.barbera@vapianofrance.fr', 'password' => Hash::make('thierry.barbera@vapianofrance.fr'), 'civility' => 'Monsieur', 'lastname' => 'Barbera', 'firstname' => 'Thierry', 'telephone' => '0650583599'],


            // ---------------------------------------------------------------- GROUPE GARNIER ---------------------------------------------------------------- //
            /* USER 15 - ID:16 */
            ['email' => 'c.vigier@dgc-mcdonalds.fr', 'password' => Hash::make('c.vigier@dgc-mcdonalds.fr'), 'civility' => 'Madame', 'lastname' => 'Vigier', 'firstname' => 'Camille', 'telephone' => '0564249560'],


            // ---------------------------------------------------------------- BURGER KING ---------------------------------------------------------------- //
            /* USER 16 - ID:17 */
            ['email' => 'p.nom@pbkn.fr', 'password' => Hash::make('p.nom@pbkn.fr'), 'civility' => 'Madame', 'lastname' => 'Nom', 'firstname' => 'Prénom', 'telephone' => ''],


            // ---------------------------------------------------------------- LAVOREL HOTELS ---------------------------------------------------------------- //
            /* USER 17 - ID:18 */
            ['email' => 'p.nom@lavorelhotels.com', 'password' => Hash::make('p.nom@lavorelhotels.com'), 'civility' => 'Madame', 'lastname' => 'Nom', 'firstname' => 'Prénom', 'telephone' => ''],


            // ---------------------------------------------------------------- GROUPE GSDI ---------------------------------------------------------------- //
            /* USER 18 - ID:19 */
            ['email' => 'claire.priolet@gsdi.com', 'password' => Hash::make('claire.priolet@gsdi.com'), 'civility' => 'Madame', 'lastname' => 'Priolet', 'firstname' => 'Claire', 'telephone' => ''],
        ];

        // Création des utilisateurs
        foreach ($users as $user) {
            User::create($user);
        }
    }
}
