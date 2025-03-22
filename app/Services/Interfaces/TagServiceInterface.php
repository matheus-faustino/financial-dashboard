<?php

namespace App\Services\Interfaces;

use Illuminate\Support\Collection;

/**
 * Tag service interface
 */
interface TagServiceInterface extends BaseServiceInterface
{
    /**
     * Get tags for a specific user
     * 
     * @param int $userId
     * @return Collection
     */
    public function getTagsForUser(int $userId): Collection;

    /**
     * Get tags sorted by usage frequency
     * 
     * @param int $userId
     * @return Collection
     */
    public function getTagsByFrequency(int $userId): Collection;

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
    public function getTagsWithTransactionCount(int $userId): Collection;

    /**
     * Create multiple tags at once
     * 
     * @param array $tagNames
     * @param int $userId
     * @return Collection
     */
    public function createMultipleTags(array $tagNames, int $userId): Collection;
}
