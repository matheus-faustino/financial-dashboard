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
     * Search tags by name
     * 
     * @param string $search
     * @param int|null $userId
     * @return Collection
     */
    public function searchTags(string $search, ?int $userId = null): Collection
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
}
