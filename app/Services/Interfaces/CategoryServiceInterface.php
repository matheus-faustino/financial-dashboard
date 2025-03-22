<?php

namespace App\Services\Interfaces;

use App\Enums\CategoryTypeEnum;
use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Category service interface
 */
interface CategoryServiceInterface extends BaseServiceInterface
{
    /**
     * Get categories for a specific user including system categories
     * 
     * @param int $userId
     * @return Collection
     */
    public function getCategoriesForUser(int $userId): Collection;

    /**
     * Get categories by type
     * 
     * @param CategoryTypeEnum $type
     * @param int|null $userId
     * @return Collection
     */
    public function getCategoriesByType(CategoryTypeEnum $type, ?int $userId = null): Collection;

    /**
     * Get only system categories
     * 
     * @return Collection
     */
    public function getSystemCategories(): Collection;

    /**
     * Get only custom categories
     * 
     * @param int|null $userId
     * @return Collection
     */
    public function getCustomCategories(?int $userId = null): Collection;

    /**
     * Get categories with transaction count
     * 
     * @param int $userId
     * @return Collection
     */
    public function getCategoriesWithTransactionCount(int $userId): Collection;

    /**
     * Get category with its transactions
     * 
     * @param int $categoryId
     * @return Model|null
     */
    public function getCategoryWithTransactions(int $categoryId): ?Model;

    /**
     * Merge two categories
     * 
     * @param int $sourceId
     * @param int $targetId
     * @return bool
     */
    public function mergeCategories(int $sourceId, int $targetId): bool;
}
