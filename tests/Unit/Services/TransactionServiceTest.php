<?php

namespace Tests\Unit\Services;

use App\Enums\CategoryTypeEnum;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TransactionService $transactionService;

    public function setUp(): void
    {
        parent::setUp();

        $this->transactionService = app(TransactionService::class);
    }

    public function testCanGetTransactionsByUser()
    {
        $user = User::factory()->create();
        $transactions = Transaction::factory()->count(5)->create(['user_id' => $user->id]);

        $anotherUser = User::factory()->create();
        Transaction::factory()->count(3)->create(['user_id' => $anotherUser->id]);

        $userTransactions = $this->transactionService->getTransactionsByUser($user->id);

        $this->assertCount(5, $userTransactions);

        foreach ($transactions as $transaction) {
            $this->assertTrue($userTransactions->contains('id', $transaction->id));
        }
    }

    public function testCanGetTransactionsBetweenDates()
    {
        $user = User::factory()->create();

        Transaction::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-01-15'
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-02-10'
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-03-05'
        ]);

        $transactions = $this->transactionService->getTransactionsBetweenDates(
            '2025-02-01',
            '2025-03-31',
            $user->id
        );

        $this->assertCount(2, $transactions);
        $this->assertEquals(['2025-02-10', '2025-03-05'], $transactions->pluck('date')->map(fn($date) => $date->format('Y-m-d'))->toArray());
    }

    public function testCanGetTransactionsByCategoryType()
    {
        $user = User::factory()->create();

        $incomeCategory = Category::factory()->type(CategoryTypeEnum::INCOME)->create();
        $expenseCategory = Category::factory()->type(CategoryTypeEnum::EXPENSE)->create();

        Transaction::factory()->count(2)->create([
            'user_id' => $user->id,
            'category_id' => $incomeCategory->id
        ]);

        Transaction::factory()->count(3)->create([
            'user_id' => $user->id,
            'category_id' => $expenseCategory->id
        ]);

        $transactions = $this->transactionService->getTransactionsByCategoryType(
            CategoryTypeEnum::EXPENSE,
            $user->id
        );

        $this->assertCount(3, $transactions);
        foreach ($transactions as $transaction) {
            $this->assertEquals($expenseCategory->id, $transaction->category_id);
        }
    }

    public function testCanGetTransactionsByCategory()
    {
        $user = User::factory()->create();

        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        Transaction::factory()->count(2)->create([
            'user_id' => $user->id,
            'category_id' => $category1->id
        ]);

        Transaction::factory()->count(3)->create([
            'user_id' => $user->id,
            'category_id' => $category2->id
        ]);

        $transactions = $this->transactionService->getTransactionsByCategory(
            $category2->id,
            $user->id
        );

        $this->assertCount(3, $transactions);
        foreach ($transactions as $transaction) {
            $this->assertEquals($category2->id, $transaction->category_id);
        }
    }

    public function testCanGetTransactionsByTag()
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create(['user_id' => $user->id]);

        $taggedTransactions = Transaction::factory()->count(3)->create([
            'user_id' => $user->id
        ]);

        Transaction::factory()->count(2)->create([
            'user_id' => $user->id
        ]);

        foreach ($taggedTransactions as $transaction) {
            $transaction->tags()->attach($tag->id);
        }

        $transactions = $this->transactionService->getTransactionsByTag($tag->id, $user->id);

        $this->assertCount(3, $transactions);
        foreach ($taggedTransactions as $transaction) {
            $this->assertTrue($transactions->contains('id', $transaction->id));
        }
    }

    public function testCanGetRecurringTransactions()
    {
        $user = User::factory()->create();

        $recurringTransactions = Transaction::factory()->recurring()->count(2)->create([
            'user_id' => $user->id
        ]);

        Transaction::factory()->count(3)->create([
            'user_id' => $user->id,
            'is_recurring' => false
        ]);

        $transactions = $this->transactionService->getRecurringTransactions($user->id);

        $this->assertCount(2, $transactions);
        foreach ($recurringTransactions as $transaction) {
            $this->assertTrue($transactions->contains('id', $transaction->id));
        }
    }

    public function testCanAddTagsToTransaction()
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $user->id]);
        $tags = Tag::factory()->count(3)->create(['user_id' => $user->id]);

        $result = $this->transactionService->addTagsToTransaction(
            $transaction->id,
            $tags->pluck('id')->toArray()
        );

        $this->assertTrue($result);

        $transaction->refresh();
        $this->assertCount(3, $transaction->tags);

        foreach ($tags as $tag) {
            $this->assertTrue($transaction->tags->contains('id', $tag->id));
        }
    }

    public function testCanRemoveTagsFromTransaction()
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $user->id]);
        $tags = Tag::factory()->count(3)->create(['user_id' => $user->id]);

        $transaction->tags()->attach($tags->pluck('id'));

        $tagToRemove = $tags->first();
        $result = $this->transactionService->removeTagsFromTransaction(
            $transaction->id,
            [$tagToRemove->id]
        );

        $this->assertTrue($result);

        $transaction->refresh();
        $this->assertCount(2, $transaction->tags);
        $this->assertFalse($transaction->tags->contains('id', $tagToRemove->id));
    }

    public function testCanGetTransactionSummary()
    {
        $user = User::factory()->create();

        $incomeCategory = Category::factory()->type(CategoryTypeEnum::INCOME)->create();
        $expenseCategory = Category::factory()->type(CategoryTypeEnum::EXPENSE)->create();
        $investmentCategory = Category::factory()->type(CategoryTypeEnum::INVESTMENT)->create();

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $incomeCategory->id,
            'amount' => 1000,
            'date' => '2025-01-10'
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $expenseCategory->id,
            'amount' => 300,
            'date' => '2025-01-15'
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $expenseCategory->id,
            'amount' => 200,
            'date' => '2025-01-20'
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $investmentCategory->id,
            'amount' => 100,
            'date' => '2025-01-25'
        ]);

        $summary = $this->transactionService->getTransactionSummary(
            $user->id,
            '2025-01-01',
            '2025-01-31'
        );

        $this->assertEquals(1000, $summary['summary']['total_income']);
        $this->assertEquals(500, $summary['summary']['total_expenses']);
        $this->assertEquals(100, $summary['summary']['total_investments']);
        $this->assertEquals(400, $summary['summary']['net_cashflow']);
        $this->assertEquals(40, $summary['summary']['savings_rate']);
        $this->assertEquals(4, $summary['transaction_count']);
        $this->assertCount(1, $summary['expenses_by_category']);
    }

    public function testCanCreateTransaction()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $transactionData = [
            'amount' => 150.75,
            'date' => '2025-03-15',
            'description' => 'Test transaction',
            'payment_method' => 'Credit Card',
            'location' => 'Online',
            'user_id' => $user->id,
            'category_id' => $category->id
        ];

        $transaction = $this->transactionService->create($transactionData);

        $this->assertNotNull($transaction);
        $this->assertEquals(150.75, $transaction->amount);
        $this->assertEquals('2025-03-15', $transaction->date->format('Y-m-d'));
        $this->assertEquals('Test transaction', $transaction->description);
        $this->assertEquals($user->id, $transaction->user_id);
        $this->assertEquals($category->id, $transaction->category_id);
    }

    public function testCanUpdateTransaction()
    {
        $transaction = Transaction::factory()->create([
            'amount' => 100,
            'description' => 'Original description'
        ]);

        $updateData = [
            'amount' => 150,
            'description' => 'Updated description'
        ];

        $result = $this->transactionService->update($updateData, $transaction->id);

        $this->assertTrue($result);

        $transaction->refresh();
        $this->assertEquals(150, $transaction->amount);
        $this->assertEquals('Updated description', $transaction->description);
    }

    public function testCanDeleteTransaction()
    {
        $transaction = Transaction::factory()->create();

        $result = $this->transactionService->delete($transaction->id);

        $this->assertTrue($result);
        $this->assertNull(Transaction::find($transaction->id));
    }
}
