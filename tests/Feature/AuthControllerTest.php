<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testLogin()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user',
                'token'
            ]);
    }

    public function testLoginWithInvalidCredentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(422);
    }

    public function testLoginInactiveUser()
    {
        $user = User::factory()->inactive()->create([
            'email' => 'inactive@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'inactive@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(422);
    }

    public function testLogout()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Successfully logged out']);
    }

    public function testGetUser()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJsonStructure(['user']);
    }

    public function testUnauthorizedAccess()
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }
}
