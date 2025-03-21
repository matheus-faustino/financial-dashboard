<?php

namespace App\Repositories;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Collection;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Get users by role
     * 
     * @param RoleEnum $role
     * @return Collection
     */
    public function findUsersByRole(RoleEnum $role): Collection
    {
        return $this->model->where('role', $role->value)->get();
    }

    /**
     * Get users managed by a specific manager
     * 
     * @param int $managerId
     * @return Collection
     */
    public function findUsersByManager(int $managerId): Collection
    {
        return $this->model->where('manager_id', $managerId)->get();
    }

    /**
     * Get active users
     * 
     * @return Collection
     */
    public function findActiveUsers(): Collection
    {
        return $this->model->where('is_active', true)->get();
    }

    /**
     * Find a user by email
     * 
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }
}
