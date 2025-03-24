<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\AddTagsRequest;
use App\Http\Requests\Transaction\RemoveTagsRequest;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionRequest;
use App\Models\Transaction;
use App\Services\Interfaces\TagServiceInterface;
use App\Services\Interfaces\TransactionServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TransactionController extends Controller
{
    protected $transactionService;
    protected $tagService;

    public function __construct(
        TransactionServiceInterface $transactionService,
        TagServiceInterface $tagService
    ) {
        $this->transactionService = $transactionService;
        $this->tagService = $tagService;

        $this->authorizeResource(Transaction::class, 'transaction');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if ($startDate && $endDate) {
            $transactions = $this->transactionService->getTransactionsBetweenDates(
                $startDate,
                $endDate,
                $request->user()->id
            );
        } else {
            $transactions = $this->transactionService->getTransactionsByUser($request->user()->id);
        }

        return response()->json(['transactions' => $transactions]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $transaction = $this->transactionService->create($data);

        // Process tags if provided
        if (array_key_exists('tags', $data) && is_array($data['tags'])) {
            // Create or get existing tags
            $tags = $this->tagService->createMultipleTags($data['tags'], $request->user()->id);
            // Attach tags to transaction
            $this->transactionService->addTagsToTransaction($transaction->id, $tags->pluck('id')->toArray());
        }

        return response()->json(['transaction' => $transaction->fresh(['category', 'tags'])], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction): JsonResponse
    {
        $transaction->load(['category', 'tags']);

        return response()->json(['transaction' => $transaction]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction): JsonResponse
    {
        $this->transactionService->update($request->validated(), $transaction->id);

        return response()->json(['transaction' => $transaction->fresh(['category', 'tags'])]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        $this->transactionService->delete($transaction->id);

        return response()->json(['message' => 'Transaction deleted successfully'], 200);
    }

    /**
     * Get transactions by category
     */
    public function byCategory(Request $request, $categoryId): JsonResponse
    {
        $transactions = $this->transactionService->getTransactionsByCategory($categoryId, $request->user()->id);

        return response()->json(['transactions' => $transactions]);
    }

    /**
     * Get transactions by tag
     */
    public function byTag(Request $request, $tagId): JsonResponse
    {
        $transactions = $this->transactionService->getTransactionsByTag($tagId, $request->user()->id);

        return response()->json(['transactions' => $transactions]);
    }

    /**
     * Get recurring transactions
     */
    public function recurring(Request $request): JsonResponse
    {
        $transactions = $this->transactionService->getRecurringTransactions($request->user()->id);

        return response()->json(['transactions' => $transactions]);
    }

    /**
     * Add tags to a transaction
     */
    public function addTags(AddTagsRequest $request, Transaction $transaction): JsonResponse
    {
        Gate::authorize('update', $transaction);

        // Create or get existing tags
        $tags = $this->tagService->createMultipleTags($request->tags, $request->user()->id);

        // Attach tags to transaction
        $this->transactionService->addTagsToTransaction($transaction->id, $tags->pluck('id')->toArray());

        return response()->json(['transaction' => $transaction->fresh(['tags'])]);
    }

    /**
     * Remove tags from a transaction
     */
    public function removeTags(RemoveTagsRequest $request, Transaction $transaction): JsonResponse
    {
        Gate::authorize('update', $transaction);

        $tagIds = $request->tags ?? null;

        // Detach tags from transaction
        $this->transactionService->removeTagsFromTransaction($transaction->id, $tagIds);

        return response()->json(['transaction' => $transaction->fresh(['tags'])]);
    }
}
