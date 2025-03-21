<?php

namespace App\Repositories\Interfaces;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Support\Collection;

interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Get users by role
     * 
     * @param RoleEnum $role
     * @return Collection
     */
    public function findUsersByRole(RoleEnum $role): Collection;

    /**
     * Get users managed by a specific manager
     * 
     * @param int $managerId
     * @return Collection
     */
    public function findUsersByManager(int $managerId): Collection;

    /**
     * Get active users
     * 
     * @return Collection
     */
    public function findActiveUsers(): Collection;

    /**
     * Find a user by email
     * 
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;
}
