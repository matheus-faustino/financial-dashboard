<?php

namespace App\Services;

use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Services\Interfaces\TagServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TagService extends BaseService implements TagServiceInterface
{
    /**
     * @var TagRepositoryInterface
     */
    protected $repository;

    /**
     * @param TagRepositoryInterface $repository
     */
    public function __construct(TagRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get tags for a specific user
     * 
     * @param int $userId
     * @return Collection
     */
    public function getTagsForUser(int $userId): Collection
    {
        return $this->repository->findTagsForUser($userId);
    }

    /**
     * Get tags sorted by usage frequency
     * 
     * @param int $userId
     * @return Collection
     */
    public function getTagsByFrequency(int $userId): Collection
    {
        return $this->repository->findTagsByFrequency($userId);
    }

    /**
     * Search tags by name
     * 
     * @param string $search
     * @param int|null $userId
     * @return Collection
     */
    public function searchTags(string $search, ?int $userId = null): Collection
    {
        return $this->repository->searchTags($search, $userId);
    }

    /**
     * Get tags with transaction count
     * 
     * @param int $userId
     * @return Collection
     */
    public function getTagsWithTransactionCount(int $userId): Collection
    {
        return $this->repository->findTagsWithTransactionCount($userId);
    }

    /**
     * Create multiple tags at once
     * 
     * @param array $tagNames
     * @param int $userId
     * @return Collection
     */
    public function createMultipleTags(array $tagNames, int $userId): Collection
    {
        $tags = collect();

        // Process tag names
        $tagNames = array_map('trim', $tagNames);
        $tagNames = array_unique($tagNames);
        $tagNames = array_filter($tagNames);

        // Get existing tags with these names to avoid duplicates
        $existingTags = $this->repository->findWhere([
            ['user_id', '=', $userId],
            ['name', 'in', $tagNames]
        ]);

        $existingTagNames = $existingTags->pluck('name')->toArray();

        // Only create tags that don't already exist
        $newTagNames = array_diff($tagNames, $existingTagNames);

        try {
            DB::beginTransaction();

            // Add existing tags to the result collection
            $tags = $tags->merge($existingTags);

            // Create new tags
            foreach ($newTagNames as $tagName) {
                $tag = $this->repository->create([
                    'name' => $tagName,
                    'user_id' => $userId
                ]);
                $tags->push($tag);
            }

            DB::commit();
            
            return $tags;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to create multiple tags: ' . $e->getMessage());
            return collect();
        }
    }
}
