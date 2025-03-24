<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\CategoryServiceInterface;
use App\Services\Interfaces\TransactionServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $transactionService;
    protected $categoryService;

    public function __construct(
        TransactionServiceInterface $transactionService,
        CategoryServiceInterface $categoryService
    ) {
        $this->transactionService = $transactionService;
        $this->categoryService = $categoryService;
    }

    /**
     * Get dashboard data
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Get transaction summary
        $summary = $this->transactionService->getTransactionSummary($userId, $startDate, $endDate);

        // Get categories with transaction count
        $categories = $this->categoryService->getCategoriesWithTransactionCount($userId);

        // Get recent transactions (limited to 5)
        $recentTransactions = $this->transactionService->getTransactionsByUser($userId)
            ->sortByDesc('date')
            ->take(5)
            ->values();

        return response()->json([
            'summary' => $summary,
            'categories' => $categories,
            'recent_transactions' => $recentTransactions,
        ]);
    }
}
