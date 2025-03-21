<?php

namespace App\Repositories;

use App\Enums\CategoryTypeEnum;
use App\Models\Category;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        $this->model = $model;
    }

    /**
     * Get categories for a specific user including system categories
     * 
     * @param int $userId
     * @return Collection
     */
    public function findCategoriesForUser(int $userId): Collection
    {
        return $this->model->forUser($userId)->get();
    }

    /**
     * Get categories by type
     * 
     * @param CategoryTypeEnum $type
     * @param int|null $userId
     * @return Collection
     */
    public function findCategoriesByType(CategoryTypeEnum $type, ?int $userId = null): Collection
    {
        $query = $this->model->ofType($type->value);

        if ($userId !== null) {
            $query->forUser($userId);
        }

        return $query->get();
    }

    /**
     * Get only system categories
     * 
     * @return Collection
     */
    public function findSystemCategories(): Collection
    {
        return $this->model->system()->get();
    }

    /**
     * Get only custom categories
     * 
     * @param int|null $userId
     * @return Collection
     */
    public function findCustomCategories(?int $userId = null): Collection
    {
        $query = $this->model->custom();

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return $query->get();
    }

    /**
     * Get categories with transaction count
     * 
     * @param int $userId
     * @return Collection
     */
    public function findCategoriesWithTransactionCount(int $userId): Collection
    {
        return $this->model->forUser($userId)
            ->withCount('transactions')
            ->get();
    }

    /**
     * Get category with its transactions
     * 
     * @param int $categoryId
     * @return Model|null
     */
    public function findCategoryWithTransactions(int $categoryId): ?Model
    {
        return $this->model->with('transactions')->find($categoryId);
    }
}
