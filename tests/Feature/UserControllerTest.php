<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndex()
    {
        $admin = User::factory()->admin()->create();
        $users = User::factory(3)->create();

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure(['users']);
    }

    public function testStore()
    {
        $admin = User::factory()->admin()->create();

        Sanctum::actingAs($admin);

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => RoleEnum::USER->value,
            'is_active' => true
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure(['user' => [
                'id',
                'name',
                'email',
                'role',
                'is_active'
            ]]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => RoleEnum::USER->value,
            'is_active' => true
        ]);
    }

    public function testShow()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        Sanctum::actingAs($admin);

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['user']);
    }

    public function testUpdate()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        Sanctum::actingAs($admin);

        $updatedData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ];

        $response = $this->putJson("/api/users/{$user->id}", $updatedData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
    }

    public function testDestroy()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'User deleted successfully']);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id
        ]);
    }

    public function testByRole()
    {
        $admin = User::factory()->admin()->create();
        $manager = User::factory()->manager()->create();
        $user = User::factory()->create();

        Sanctum::actingAs($admin);

        $response = $this->getJson("/api/users/by-role/" . RoleEnum::MANAGER->value);

        $response->assertStatus(200);
        $users = json_decode($response->getContent(), true)['users'];
        $this->assertEquals(1, count($users));
        $this->assertEquals($manager->id, $users[0]['id']);
    }

    public function testByManager()
    {
        $manager = User::factory()->manager()->create();
        $user1 = User::factory()->create(['manager_id' => $manager->id]);
        $user2 = User::factory()->create(['manager_id' => $manager->id]);
        $user3 = User::factory()->create(); // No manager

        Sanctum::actingAs($manager);

        $response = $this->getJson("/api/users/by-manager/{$manager->id}");

        $response->assertStatus(200);
        $users = json_decode($response->getContent(), true)['users'];
        $this->assertEquals(2, count($users));
    }

    public function testUpdateStatus()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['is_active' => true]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/users/{$user->id}/status", [
            'is_active' => false
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'User deactivated successfully']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_active' => false
        ]);
    }

    public function testChangePassword()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        Sanctum::actingAs($admin);

        $response = $this->postJson("/api/users/{$user->id}/password", [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Password changed successfully']);

        // Fetch updated user from database
        $updatedUser = User::find($user->id);
        $this->assertTrue(Hash::check('newpassword123', $updatedUser->password));
    }

    public function testCannotDeleteSelf()
    {
        $admin = User::factory()->admin()->create();

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/users/{$admin->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('users', [
            'id' => $admin->id
        ]);
    }

    public function testCannotDeactivateSelf()
    {
        $admin = User::factory()->admin()->create(['is_active' => true]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/users/{$admin->id}/status", [
            'is_active' => false
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'You cannot deactivate your own account']);

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'is_active' => true
        ]);
    }

    public function testManagerCanOnlyCreateUserRole()
    {
        $manager = User::factory()->manager()->create();

        Sanctum::actingAs($manager);

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => RoleEnum::MANAGER->value,
            'is_active' => true
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(422);
    }

    public function testRegularUserCannotAccessUserEndpoints()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/users');

        $response->assertStatus(403);
    }

    public function testManagerCanManageAssignedUsers()
    {
        $manager = User::factory()->manager()->create();
        $user = User::factory()->create(['manager_id' => $manager->id]);

        Sanctum::actingAs($manager);

        // View managed user
        $viewResponse = $this->getJson("/api/users/{$user->id}");
        $viewResponse->assertStatus(200);

        // Update managed user
        $updateResponse = $this->putJson("/api/users/{$user->id}", [
            'name' => 'Updated By Manager'
        ]);
        $updateResponse->assertStatus(200);

        // Delete managed user
        $deleteResponse = $this->deleteJson("/api/users/{$user->id}");
        $deleteResponse->assertStatus(200);
    }

    public function testManagerCannotManageUnassignedUsers()
    {
        $manager = User::factory()->manager()->create();
        $user = User::factory()->create(); // Not assigned to manager

        Sanctum::actingAs($manager);

        // Cannot view unmanaged user
        $viewResponse = $this->getJson("/api/users/{$user->id}");
        $viewResponse->assertStatus(403);

        // Cannot update unmanaged user
        $updateResponse = $this->putJson("/api/users/{$user->id}", [
            'name' => 'Updated By Manager'
        ]);
        $updateResponse->assertStatus(403);

        // Cannot delete unmanaged user
        $deleteResponse = $this->deleteJson("/api/users/{$user->id}");
        $deleteResponse->assertStatus(403);
    }
}
