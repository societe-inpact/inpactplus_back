<?php

namespace Tests\Feature;

use App\Models\Misc\Role;
use App\Models\Misc\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_the_authenticated_user_with_permissions()
    {
        // Créer un utilisateur avec un rôle
        $user = User::factory()->create();
        $role = Role::create(['name' => 'client']);
        $user->assignRole($role);

        // Simuler une authentification
        $this->actingAs($user);

        // Appeler la méthode
        $response = $this->getJson('/api/user');

        // Vérifier que la réponse est correcte
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'L\'utilisateur a été chargé avec succès',
                'status' => 200,
            ]);
    }

    public function test_it_allows_a_user_to_login()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Connexion réussie',
            ]);

        // Vérifier la présence du cookie
        $this->assertNotNull($response->headers->getCookies());
    }

    public function test_it_registers_a_new_user()
    {
        $data = [
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'civility' => 'Mr',
            'lastname' => 'Doe',
            'firstname' => 'John',
            'telephone' => '0600000000',
            'is_employee' => false,
        ];

        $response = $this->postJson('/api/register', $data);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Utilisateur créé avec succès',
            ]);

        // Vérifie que l'utilisateur est bien en base de données
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
        ]);
    }

    public function test_it_logs_out_the_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(204);

        // Vérifie que le cookie JWT est supprimé
        $this->assertNull(Cookie::get('jwt'));
    }

}
