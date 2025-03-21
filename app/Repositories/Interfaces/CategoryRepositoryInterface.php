<?php

namespace App\Repositories\Interfaces;

use App\Enums\CategoryTypeEnum;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface extends RepositoryInterface
{
    /**
     * Get categories for a specific user including system categories
     * 
     * @param int $userId
     * @return Collection
     */
    public function findCategoriesForUser(int $userId): Collection;

    /**
     * Get categories by type
     * 
     * @param CategoryTypeEnum $type
     * @param int|null $userId
     * @return Collection
     */
    public function findCategoriesByType(CategoryTypeEnum $type, ?int $userId = null): Collection;

    /**
     * Get only system categories
     * 
     * @return Collection
     */
    public function findSystemCategories(): Collection;

    /**
     * Get only custom categories
     * 
     * @param int|null $userId
     * @return Collection
     */
    public function findCustomCategories(?int $userId = null): Collection;

    /**
     * Get categories with transaction count
     * 
     * @param int $userId
     * @return Collection
     */
    public function findCategoriesWithTransactionCount(int $userId): Collection;

    /**
     * Get category with its transactions
     * 
     * @param int $categoryId
     * @return mixed
     */
    public function findCategoryWithTransactions(int $categoryId);
}
