<?php

namespace App\Services;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class UserService extends BaseService implements UserServiceInterface
{
    /**
     * @var UserRepositoryInterface
     */
    protected $repository;

    /**
     * @param UserRepositoryInterface $repository
     */
    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get users by role
     * 
     * @param RoleEnum $role
     * @return Collection
     */
    public function getUsersByRole(RoleEnum $role): Collection
    {
        return $this->repository->findUsersByRole($role);
    }

    /**
     * Get users managed by a specific manager
     * 
     * @param int $managerId
     * @return Collection
     */
    public function getUsersByManager(int $managerId): Collection
    {
        return $this->repository->findUsersByManager($managerId);
    }

    /**
     * Get active users
     * 
     * @return Collection
     */
    public function getActiveUsers(): Collection
    {
        return $this->repository->findActiveUsers();
    }

    /**
     * Find a user by email
     * 
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->repository->findByEmail($email);
    }

    /**
     * Register a new user
     * 
     * @param array $userData
     * @param int|null $managerId
     * @return User
     */
    public function registerUser(array $userData, ?int $managerId = null): User
    {
        // Ensure password is hashed
        if (isset($userData['password'])) {
            $userData['password'] = Hash::make($userData['password']);
        }

        // Set manager ID if provided
        if ($managerId !== null) {
            $userData['manager_id'] = $managerId;
        }

        // Set default values if not provided
        $userData['is_active'] = $userData['is_active'] ?? true;
        $userData['role'] = $userData['role'] ?? RoleEnum::USER->value;

        return $this->repository->create($userData);
    }

    /**
     * Update user status (active/inactive)
     * 
     * @param int $userId
     * @param bool $isActive
     * @return bool
     */
    public function updateUserStatus(int $userId, bool $isActive): bool
    {
        return $this->repository->update(['is_active' => $isActive], $userId);
    }

    /**
     * Change user password
     * 
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function changePassword(int $userId, string $newPassword): bool
    {
        $hashedPassword = Hash::make($newPassword);
        
        return $this->repository->update(['password' => $hashedPassword], $userId);
    }
}
