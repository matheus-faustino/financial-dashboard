<?php

namespace App\Repositories\Interfaces;

use Illuminate\Support\Collection;

interface TagRepositoryInterface extends RepositoryInterface
{
    /**
     * Get tags for a specific user
     * 
     * @param int $userId
     * @return Collection
     */
    public function findTagsForUser(int $userId): Collection;

    /**
     * Get tags sorted by usage frequency
     * 
     * @param int $userId
     * @return Collection
     */
    public function findTagsByFrequency(int $userId): Collection;

    /**
     * Search tags by name
     * 
     * @param string $search
     * @param int|null $userId
     * @return Collection
     */
    public function searchTags(string $search, ?int $userId = null): Collection;

    /**
     * Get tags with transaction count
     * 
     * @param int $userId
     * @return Collection
     */
    public function findTagsWithTransactionCount(int $userId): Collection;
}
