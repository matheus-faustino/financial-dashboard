<?php

namespace App\Repositories\Interfaces;

use App\Enums\CategoryTypeEnum;
use Illuminate\Support\Collection;

interface TransactionRepositoryInterface extends RepositoryInterface
{
    /**
     * Get transactions for a specific user
     * 
     * @param int $userId
     * @return Collection
     */
    public function findTransactionsByUser(int $userId): Collection;

    /**
     * Get transactions between dates
     * 
     * @param string $startDate
     * @param string $endDate
     * @param int|null $userId
     * @return Collection
     */
    public function findTransactionsBetweenDates(string $startDate, string $endDate, ?int $userId = null): Collection;

    /**
     * Get transactions by category type
     * 
     * @param CategoryTypeEnum $type
     * @param int|null $userId
     * @return Collection
     */
    public function findTransactionsByCategoryType(CategoryTypeEnum $type, ?int $userId = null): Collection;

    /**
     * Get transactions by category
     * 
     * @param int $categoryId
     * @param int|null $userId
     * @return Collection
     */
    public function findTransactionsByCategory(int $categoryId, ?int $userId = null): Collection;

    /**
     * Get transactions by tag
     * 
     * @param int $tagId
     * @param int|null $userId
     * @return Collection
     */
    public function findTransactionsByTag(int $tagId, ?int $userId = null): Collection;

    /**
     * Get recurring transactions
     * 
     * @param int|null $userId
     * @return Collection
     */
    public function findRecurringTransactions(?int $userId = null): Collection;

    /**
     * Attach tags to a transaction
     * 
     * @param int $transactionId
     * @param array $tagIds
     * @return bool
     */
    public function attachTags(int $transactionId, array $tagIds): bool;

    /**
     * Detach tags from a transaction
     * 
     * @param int $transactionId
     * @param array|null $tagIds
     * @return bool
     */
    public function detachTags(int $transactionId, ?array $tagIds = null): bool;
}
