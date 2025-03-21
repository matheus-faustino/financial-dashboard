<?php

namespace App\Repositories;

use App\Enums\CategoryTypeEnum;
use App\Models\Transaction;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use Illuminate\Support\Collection;

class TransactionRepository extends BaseRepository implements TransactionRepositoryInterface
{
    public function __construct(Transaction $model)
    {
        $this->model = $model;
    }

    /**
     * Get transactions for a specific user
     * 
     * @param int $userId
     * @return Collection
     */
    public function findTransactionsByUser(int $userId): Collection
    {
        return $this->model->forUser($userId)->get();
    }

    /**
     * Get transactions between dates
     * 
     * @param string $startDate
     * @param string $endDate
     * @param int|null $userId
     * @return Collection
     */
    public function findTransactionsBetweenDates(string $startDate, string $endDate, ?int $userId = null): Collection
    {
        $query = $this->model->betweenDates($startDate, $endDate);

        if ($userId !== null) {
            $query->forUser($userId);
        }

        return $query->get();
    }

    /**
     * Get transactions by category type
     * 
     * @param CategoryTypeEnum $type
     * @param int|null $userId
     * @return Collection
     */
    public function findTransactionsByCategoryType(CategoryTypeEnum $type, ?int $userId = null): Collection
    {
        $query = $this->model->ofCategoryType($type->value);

        if ($userId !== null) {
            $query->forUser($userId);
        }

        return $query->get();
    }

    /**
     * Get transactions by category
     * 
     * @param int $categoryId
     * @param int|null $userId
     * @return Collection
     */
    public function findTransactionsByCategory(int $categoryId, ?int $userId = null): Collection
    {
        $query = $this->model->where('category_id', $categoryId);

        if ($userId !== null) {
            $query->forUser($userId);
        }

        return $query->get();
    }

    /**
     * Get transactions by tag
     * 
     * @param int $tagId
     * @param int|null $userId
     * @return Collection
     */
    public function findTransactionsByTag(int $tagId, ?int $userId = null): Collection
    {
        $query = $this->model->whereHas('tags', function ($q) use ($tagId) {
            $q->where('tag_id', $tagId);
        });

        if ($userId !== null) {
            $query->forUser($userId);
        }

        return $query->get();
    }

    /**
     * Get recurring transactions
     * 
     * @param int|null $userId
     * @return Collection
     */
    public function findRecurringTransactions(?int $userId = null): Collection
    {
        $query = $this->model->where('is_recurring', true);

        if ($userId !== null) {
            $query->forUser($userId);
        }

        return $query->get();
    }

    /**
     * Attach tags to a transaction
     * 
     * @param int $transactionId
     * @param array $tagIds
     * @return bool
     */
    public function attachTags(int $transactionId, array $tagIds): bool
    {
        $transaction = $this->find($transactionId);

        if (!$transaction) {
            return false;
        }

        $transaction->tags()->attach($tagIds);
        return true;
    }

    /**
     * Detach tags from a transaction
     * 
     * @param int $transactionId
     * @param array|null $tagIds
     * @return bool
     */
    public function detachTags(int $transactionId, ?array $tagIds = null): bool
    {
        $transaction = $this->find($transactionId);

        if (!$transaction) {
            return false;
        }

        $transaction->tags()->detach($tagIds);
        return true;
    }
}
