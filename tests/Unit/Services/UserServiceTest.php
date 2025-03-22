<?php

namespace Tests\Unit\Services;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $userService;

    public function setUp(): void
    {
        parent::setUp();

        $this->userService = app(UserService::class);
    }

    public function testCanGetUsersByRole()
    {
        $managerUsers = User::factory()->manager()->count(3)->create();

        User::factory()->count(4)->create();
        User::factory()->admin()->count(2)->create();

        $users = $this->userService->getUsersByRole(RoleEnum::MANAGER);

        $this->assertCount(3, $users);

        foreach ($managerUsers as $manager) {
            $this->assertTrue($users->contains('id', $manager->id));
        }
    }

    public function testCanGetUsersByManager()
    {
        $manager = User::factory()->manager()->create();

        $managedUsers = User::factory()->count(3)->create(['manager_id' => $manager->id]);

        User::factory()->count(2)->create();

        $users = $this->userService->getUsersByManager($manager->id);

        $this->assertCount(3, $users);

        foreach ($managedUsers as $user) {
            $this->assertTrue($users->contains('id', $user->id));
        }
    }

    public function testCanGetActiveUsers()
    {
        $activeUsers = User::factory()->count(3)->create(['is_active' => true]);
        User::factory()->inactive()->count(2)->create();

        $users = $this->userService->getActiveUsers();

        $this->assertEquals(3, $users->count());

        foreach ($activeUsers as $user) {
            $this->assertTrue($users->contains('id', $user->id));
        }
    }

    public function testCanFindUserByEmail()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        $foundUser = $this->userService->findByEmail('test@example.com');

        $this->assertNotNull($foundUser);
        $this->assertEquals($user->id, $foundUser->id);
        $this->assertEquals('test@example.com', $foundUser->email);
    }

    public function testReturnsNullForNonExistentEmail()
    {
        $user = $this->userService->findByEmail('nonexistent@example.com');

        $this->assertNull($user);
    }

    public function test_can_register_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'password123'
        ];

        $user = $this->userService->registerUser($userData);

        $this->assertNotNull($user);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('newuser@example.com', $user->email);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertEquals(RoleEnum::USER, $user->role);
        $this->assertTrue($user->is_active);
    }

    public function testCanRegisterUserWithManager()
    {
        $manager = User::factory()->manager()->create();

        $userData = [
            'name' => 'Managed User',
            'email' => 'managed@example.com',
            'password' => 'password123'
        ];

        $user = $this->userService->registerUser($userData, $manager->id);

        $this->assertNotNull($user);
        $this->assertEquals('Managed User', $user->name);
        $this->assertEquals('managed@example.com', $user->email);
        $this->assertEquals($manager->id, $user->manager_id);
    }

    public function testCanUpdateUserStatus()
    {
        $user = User::factory()->create(['is_active' => true]);

        $result = $this->userService->updateUserStatus($user->id, false);

        $this->assertTrue($result);

        $user->refresh();
        $this->assertFalse($user->is_active);

        $result = $this->userService->updateUserStatus($user->id, true);

        $this->assertTrue($result);

        $user->refresh();
        $this->assertTrue($user->is_active);
    }

    public function testCanChangePassword()
    {
        $user = User::factory()->create([
            'password' => Hash::make('original_password')
        ]);

        $result = $this->userService->changePassword($user->id, 'new_password');

        $this->assertTrue($result);

        $user->refresh();
        $this->assertTrue(Hash::check('new_password', $user->password));
        $this->assertFalse(Hash::check('original_password', $user->password));
    }

    public function testCanCreateUser()
    {
        $userData = [
            'name' => 'Created User',
            'email' => 'created@example.com',
            'password' => Hash::make('password123'),
            'role' => RoleEnum::MANAGER->value
        ];

        $user = $this->userService->create($userData);

        $this->assertNotNull($user);
        $this->assertEquals('Created User', $user->name);
        $this->assertEquals('created@example.com', $user->email);
        $this->assertEquals(RoleEnum::MANAGER, $user->role);
    }

    public function testCanUpdateUser()
    {
        $user = User::factory()->create([
            'name' => 'Original Name'
        ]);

        $updateData = [
            'name' => 'Updated Name'
        ];

        $result = $this->userService->update($updateData, $user->id);

        $this->assertTrue($result);

        $user->refresh();
        $this->assertEquals('Updated Name', $user->name);
    }

    public function testCanDeleteUser()
    {
        $user = User::factory()->create();

        $result = $this->userService->delete($user->id);

        $this->assertTrue($result);
        $this->assertNull(User::find($user->id));
    }
}
