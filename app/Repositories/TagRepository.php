<?php

namespace App\Repositories;

use App\Models\Tag;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Support\Collection;

class TagRepository extends BaseRepository implements TagRepositoryInterface
{
    public function __construct(Tag $model)
    {
        $this->model = $model;
    }

    /**
     * Get tags for a specific user
     * 
     * @param int $userId
     * @return Collection
     */
    public function findTagsForUser(int $userId): Collection
    {
        return $this->model->forUser($userId)->get();
    }

    /**
     * Get tags sorted by usage frequency
     * 
     * @param int $userId
     * @return Collection
     */
    public function findTagsByFrequency(int $userId): Collection
    {
        return $this->model->forUser($userId)->byFrequency()->get();
    }

    /**
     * Get tags based on a search criteria
     * 
     * @param string $search
     * @param int|null $userId
     * @return Collection
     */
    public function findTagsBySearch(string $search, ?int $userId = null): Collection
    {
        $query = $this->model->search($search);

        if ($userId !== null) {
            $query->forUser($userId);
        }

        return $query->get();
    }

    /**
     * Get tags with transaction count
     * 
     * @param int $userId
     * @return Collection
     */
    public function findTagsWithTransactionCount(int $userId): Collection
    {
        return $this->model->forUser($userId)
            ->withCount('transactions')
            ->get();
    }

    /**
     * Get tags by user and names
     * 
     * @param int $userId
     * @param array $tagNames
     * @return Collection
     */
    public function findTagsByNames(int $userId, array $tagNames): Collection
    {
        return $this->model->forUser($userId)
            ->whereIn('name', $tagNames)
            ->get();
    }
}
