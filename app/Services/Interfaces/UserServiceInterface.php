<?php

namespace App\Services\Interfaces;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * User service interface
 */
interface UserServiceInterface extends BaseServiceInterface
{
    /**
     * Get users by role
     * 
     * @param RoleEnum $role
     * @return Collection
     */
    public function getUsersByRole(RoleEnum $role): Collection;

    /**
     * Get users managed by a specific manager
     * 
     * @param int $managerId
     * @return Collection
     */
    public function getUsersByManager(int $managerId): Collection;

    /**
     * Get active users
     * 
     * @return Collection
     */
    public function getActiveUsers(): Collection;

    /**
     * Find a user by email
     * 
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Register a new user 
     * 
     * @param array $userData
     * @param int|null $managerId
     * @return User
     */
    public function registerUser(array $userData, ?int $managerId = null): User;

    /**
     * Update user status (active/inactive)
     * 
     * @param int $userId
     * @param bool $isActive
     * @return bool
     */
    public function updateUserStatus(int $userId, bool $isActive): bool;

    /**
     * Change user password
     * 
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function changePassword(int $userId, string $newPassword): bool;
}
