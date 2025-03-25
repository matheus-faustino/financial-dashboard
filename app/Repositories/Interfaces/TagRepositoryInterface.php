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
     * Get tags based on a search criteria
     * 
     * @param string $search
     * @param int|null $userId
     * @return Collection
     */
    public function findTagsBySearch(string $search, ?int $userId = null): Collection;

    /**
     * Get tags with transaction count
     * 
     * @param int $userId
     * @return Collection
     */
    public function findTagsWithTransactionCount(int $userId): Collection;

    /**
     * Get tags by user and names
     * 
     * @param int $userId
     * @param array $tagNames
     * @return Collection
     */
    public function findTagsByNames(int $userId, array $tagNames): Collection;
}
