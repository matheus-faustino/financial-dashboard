<?php

namespace App\Services\Interfaces;

use App\Enums\CategoryTypeEnum;
use Illuminate\Support\Collection;

/**
 * Transaction service interface
 */
interface TransactionServiceInterface extends BaseServiceInterface
{
    /**
     * Get transactions for a specific user
     * 
     * @param int $userId
     * @return Collection
     */
    public function getTransactionsByUser(int $userId): Collection;

    /**
     * Get transactions between dates
     * 
     * @param string $startDate
     * @param string $endDate
     * @param int|null $userId
     * @return Collection
     */
    public function getTransactionsBetweenDates(string $startDate, string $endDate, ?int $userId = null): Collection;

    /**
     * Get transactions by category type
     * 
     * @param CategoryTypeEnum $type
     * @param int|null $userId
     * @return Collection
     */
    public function getTransactionsByCategoryType(CategoryTypeEnum $type, ?int $userId = null): Collection;

    /**
     * Get transactions by category
     * 
     * @param int $categoryId
     * @param int|null $userId
     * @return Collection
     */
    public function getTransactionsByCategory(int $categoryId, ?int $userId = null): Collection;

    /**
     * Get transactions by tag
     * 
     * @param int $tagId
     * @param int|null $userId
     * @return Collection
     */
    public function getTransactionsByTag(int $tagId, ?int $userId = null): Collection;

    /**
     * Get recurring transactions
     * 
     * @param int|null $userId
     * @return Collection
     */
    public function getRecurringTransactions(?int $userId = null): Collection;

    /**
     * Add tags to a transaction
     * 
     * @param int $transactionId
     * @param array $tagIds
     * @return bool
     */
    public function addTagsToTransaction(int $transactionId, array $tagIds): bool;

    /**
     * Remove tags from a transaction
     * 
     * @param int $transactionId
     * @param array|null $tagIds
     * @return bool
     */
    public function removeTagsFromTransaction(int $transactionId, ?array $tagIds = null): bool;

    /**
     * Calculate summary statistics for user transactions
     * 
     * @param int $userId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getTransactionSummary(int $userId, ?string $startDate = null, ?string $endDate = null): array;
}
