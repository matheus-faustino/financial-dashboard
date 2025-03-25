<?php

namespace App\Services;

use App\Enums\CategoryTypeEnum;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use App\Services\Interfaces\TransactionServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class TransactionService extends BaseService implements TransactionServiceInterface
{
    /**
     * @var TransactionRepositoryInterface
     */
    protected $repository;

    /**
     * @param TransactionRepositoryInterface $repository
     */
    public function __construct(TransactionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get transactions for a specific user
     * 
     * @param int $userId
     * @return Collection
     */
    public function getTransactionsByUser(int $userId): Collection
    {
        return $this->repository->findTransactionsByUser($userId);
    }

    /**
     * Get transactions between dates
     * 
     * @param string $startDate
     * @param string $endDate
     * @param int|null $userId
     * @return Collection
     */
    public function getTransactionsBetweenDates(string $startDate, string $endDate, ?int $userId = null): Collection
    {
        return $this->repository->findTransactionsBetweenDates($startDate, $endDate, $userId);
    }

    /**
     * Get transactions by category type
     * 
     * @param CategoryTypeEnum $type
     * @param int|null $userId
     * @return Collection
     */
    public function getTransactionsByCategoryType(CategoryTypeEnum $type, ?int $userId = null): Collection
    {
        return $this->repository->findTransactionsByCategoryType($type, $userId);
    }

    /**
     * Get transactions by category
     * 
     * @param int $categoryId
     * @param int|null $userId
     * @return Collection
     */
    public function getTransactionsByCategory(int $categoryId, ?int $userId = null): Collection
    {
        return $this->repository->findTransactionsByCategory($categoryId, $userId);
    }

    /**
     * Get transactions by tag
     * 
     * @param int $tagId
     * @param int|null $userId
     * @return Collection
     */
    public function getTransactionsByTag(int $tagId, ?int $userId = null): Collection
    {
        return $this->repository->findTransactionsByTag($tagId, $userId);
    }

    /**
     * Get recurring transactions
     * 
     * @param int|null $userId
     * @return Collection
     */
    public function getRecurringTransactions(?int $userId = null): Collection
    {
        return $this->repository->findRecurringTransactions($userId);
    }

    /**
     * Add tags to a transaction
     * 
     * @param int $transactionId
     * @param array $tagIds
     * @return bool
     */
    public function addTagsToTransaction(int $transactionId, array $tagIds): bool
    {
        $transaction = $this->repository->find($transactionId);

        if (!$transaction) {
            throw new ModelNotFoundException("Transaction with ID {$transactionId} was not found in database.");
        }

        $transaction->tags()->attach($tagIds);

        return true;
    }

    /**
     * Remove tags from a transaction
     * 
     * @param int $transactionId
     * @param array|null $tagIds
     * @return bool
     */
    public function removeTagsFromTransaction(int $transactionId, ?array $tagIds = null): bool
    {
        $transaction = $this->repository->find($transactionId);

        if (!$transaction) {
            throw new ModelNotFoundException("Transaction with ID {$transactionId} was not found in database.");
        }

        $transaction->tags()->detach($tagIds);

        return true;
    }

    /**
     * Calculate summary statistics for user transactions
     * 
     * @param int $userId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getTransactionSummary(int $userId, ?string $startDate = null, ?string $endDate = null): array
    {
        // Define default date range if not provided (current month)
        if ($startDate === null || $endDate === null) {
            $startDate = date('Y-m-01'); // First day of current month
            $endDate = date('Y-m-t'); // Last day of current month
        }

        // Get transactions within date range
        $transactions = $this->getTransactionsBetweenDates($startDate, $endDate, $userId);

        // Calculate income transactions
        $incomeTransactions = $transactions->filter(function ($transaction) {
            return $transaction->category->type->value === CategoryTypeEnum::INCOME->value;
        });

        // Calculate expense transactions
        $expenseTransactions = $transactions->filter(function ($transaction) {
            return $transaction->category->type->value === CategoryTypeEnum::EXPENSE->value;
        });

        // Calculate investment transactions
        $investmentTransactions = $transactions->filter(function ($transaction) {
            return $transaction->category->type->value === CategoryTypeEnum::INVESTMENT->value;
        });

        // Calculate totals
        $totalIncome = $incomeTransactions->sum('amount');
        $totalExpenses = $expenseTransactions->sum('amount');
        $totalInvestments = $investmentTransactions->sum('amount');
        $netCashflow = $totalIncome - $totalExpenses - $totalInvestments;

        // Group expenses by category
        $expensesByCategory = $expenseTransactions->groupBy('category_id')
            ->map(function ($transactions) {
                return [
                    'category' => $transactions->first()->category->name,
                    'amount' => $transactions->sum('amount'),
                    'percentage' => 0, // Will calculate below
                ];
            })->values()->toArray();

        // Calculate percentage for each category
        if ($totalExpenses > 0) {
            foreach ($expensesByCategory as &$category) {
                $category['percentage'] = round(($category['amount'] / $totalExpenses) * 100, 2);
            }
        }

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => [
                'total_income' => $totalIncome,
                'total_expenses' => $totalExpenses,
                'total_investments' => $totalInvestments,
                'net_cashflow' => $netCashflow,
                'savings_rate' => $totalIncome > 0 ? round(($netCashflow / $totalIncome) * 100, 2) : 0,
            ],
            'expenses_by_category' => $expensesByCategory,
            'transaction_count' => $transactions->count(),
        ];
    }
}
