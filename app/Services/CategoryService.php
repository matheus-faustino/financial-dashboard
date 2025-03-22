<?php

namespace App\Services;

use App\Enums\CategoryTypeEnum;
use App\Models\Transaction;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use App\Services\Interfaces\CategoryServiceInterface;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryService extends BaseService implements CategoryServiceInterface
{
    /**
     * @var CategoryRepositoryInterface
     */
    protected $repository;

    /**
     * @var TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * @param CategoryRepositoryInterface $repository
     * @param TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(
        CategoryRepositoryInterface $repository,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->repository = $repository;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Get categories for a specific user including system categories
     * 
     * @param int $userId
     * @return Collection
     */
    public function getCategoriesForUser(int $userId): Collection
    {
        return $this->repository->findCategoriesForUser($userId);
    }

    /**
     * Get categories by type
     * 
     * @param CategoryTypeEnum $type
     * @param int|null $userId
     * @return Collection
     */
    public function getCategoriesByType(CategoryTypeEnum $type, ?int $userId = null): Collection
    {
        return $this->repository->findCategoriesByType($type, $userId);
    }

    /**
     * Get only system categories
     * 
     * @return Collection
     */
    public function getSystemCategories(): Collection
    {
        return $this->repository->findSystemCategories();
    }

    /**
     * Get only custom categories
     * 
     * @param int|null $userId
     * @return Collection
     */
    public function getCustomCategories(?int $userId = null): Collection
    {
        return $this->repository->findCustomCategories($userId);
    }

    /**
     * Get categories with transaction count
     * 
     * @param int $userId
     * @return Collection
     */
    public function getCategoriesWithTransactionCount(int $userId): Collection
    {
        return $this->repository->findCategoriesWithTransactionCount($userId);
    }

    /**
     * Get category with its transactions
     * 
     * @param int $categoryId
     * @return Model|null
     */
    public function getCategoryWithTransactions(int $categoryId): ?Model
    {
        return $this->repository->findCategoryWithTransactions($categoryId);
    }

    /**
     * Merge two categories
     * 
     * @param int $sourceId
     * @param int $targetId
     * @return bool
     */
    public function mergeCategories(int $sourceId, int $targetId): bool
    {
        // Get the categories to ensure they exist
        $sourceCategory = $this->findById($sourceId);
        $targetCategory = $this->findById($targetId);

        if (!$sourceCategory || !$targetCategory) {
            return false;
        }

        // Don't allow merging system categories
        if ($sourceCategory->is_system) {
            return false;
        }

        try {
            return DB::transaction(function () use ($sourceId, $targetId) {
                // Update all transactions from the source category to the target category

                Transaction::where('category_id', $sourceId)
                    ->update(['category_id' => $targetId]);

                // Delete the source category
                return $this->delete($sourceId);
            });
        } catch (Exception $e) {
            Log::error('Failed to merge categories: ' . $e->getMessage());

            return false;
        }
    }
}
