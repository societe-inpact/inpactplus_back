<?php

namespace Database\Seeders;

use App\Models\Companies\Company;
use App\Models\Misc\User;
use Faker\Factory;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        $companies = [
            ['name' => 'Vapiano', 'description' => 'Description pour Vapiano', 'telephone' => '0101010101', 'notes' => 'Notes pour Vapiano', 'referent_id' => $users[0]->id],
            ['name' => 'Groupe Garnier', 'description' => 'Description pour Groupe Garnier', 'telephone' => '0101010101', 'notes' => 'Notes pour Groupe Garnier', 'referent_id' => $users[1]->id],
            ['name' => 'Burger King', 'description' => 'Description pour Burger King', 'telephone' => '0101010101', 'notes' => 'Notes pour Burger King', 'referent_id' => $users[2]->id],
            ['name' => 'Lavorel Hotels', 'description' => 'Description pour Lavorel Hotels', 'telephone' => '0101010101', 'notes' => 'Notes pour Lavorel Hotels', 'referent_id' => $users[3]->id],
            ['name' => 'Groupe GSDI', 'description' => 'Description pour Groupe GSDI', 'telephone' => '0101010101', 'notes' => 'Notes pour Groupe GSDI', 'referent_id' => $users[4]->id],
        ];

        foreach ($companies as $index => $company) {
            $createdCompany = Company::create($company);
            $users[$index]->update(['company_id' => $createdCompany->id]);
        }
    }
}
