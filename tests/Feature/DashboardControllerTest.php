<?php

namespace Tests\Feature;

use App\Enums\CategoryTypeEnum;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndex()
    {
        $user = User::factory()->create();

        $incomeCategory = Category::factory()->type(CategoryTypeEnum::INCOME)->create(['user_id' => $user->id]);
        $expenseCategory = Category::factory()->type(CategoryTypeEnum::EXPENSE)->create(['user_id' => $user->id]);
        $investmentCategory = Category::factory()->type(CategoryTypeEnum::INVESTMENT)->create(['user_id' => $user->id]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $incomeCategory->id,
            'amount' => 1000,
            'date' => now()->subDays(5)
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $expenseCategory->id,
            'amount' => 500,
            'date' => now()->subDays(3)
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $investmentCategory->id,
            'amount' => 200,
            'date' => now()->subDays(1)
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'summary' => [
                    'period',
                    'summary' => [
                        'total_income',
                        'total_expenses',
                        'total_investments',
                        'net_cashflow',
                        'savings_rate'
                    ],
                    'expenses_by_category',
                    'transaction_count'
                ],
                'categories',
                'recent_transactions'
            ]);

        $data = json_decode($response->getContent(), true);

        // Check summary calculations
        $this->assertEquals(1000, $data['summary']['summary']['total_income']);
        $this->assertEquals(500, $data['summary']['summary']['total_expenses']);
        $this->assertEquals(200, $data['summary']['summary']['total_investments']);
        $this->assertEquals(300, $data['summary']['summary']['net_cashflow']); // 1000 - 500 - 200
        $this->assertEquals(30, $data['summary']['summary']['savings_rate']); // (300/1000) * 100

        // Check recent transactions
        $this->assertCount(3, $data['recent_transactions']);
    }

    public function testIndexWithDateRange()
    {
        $user = User::factory()->create();

        // Create categories
        $incomeCategory = Category::factory()->type(CategoryTypeEnum::INCOME)->create(['user_id' => $user->id]);
        $expenseCategory = Category::factory()->type(CategoryTypeEnum::EXPENSE)->create(['user_id' => $user->id]);

        // Create transactions in different months
        // January transaction
        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $incomeCategory->id,
            'amount' => 2000,
            'date' => '2025-01-15'
        ]);

        // January transaction
        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $expenseCategory->id,
            'amount' => 1000,
            'date' => '2025-01-20'
        ]);

        // February transaction
        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $incomeCategory->id,
            'amount' => 3000,
            'date' => '2025-02-10'
        ]);

        Sanctum::actingAs($user);

        // Request dashboard with January date range
        $response = $this->getJson('/api/dashboard?start_date=2025-01-01&end_date=2025-01-31');

        $response->assertStatus(200);

        $data = json_decode($response->getContent(), true);

        // Check that only January transactions are included
        $this->assertEquals(2000, $data['summary']['summary']['total_income']);
        $this->assertEquals(1000, $data['summary']['summary']['total_expenses']);
        $this->assertEquals(2, $data['summary']['transaction_count']);
    }

    public function testDashboardDataIsUserSpecific()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create categories
        $incomeCategory1 = Category::factory()->type(CategoryTypeEnum::INCOME)->create(['user_id' => $user1->id]);
        $incomeCategory2 = Category::factory()->type(CategoryTypeEnum::INCOME)->create(['user_id' => $user2->id]);

        // Create transactions for different users
        Transaction::factory()->create([
            'user_id' => $user1->id,
            'category_id' => $incomeCategory1->id,
            'amount' => 1000,
            'date' => now()->subDays(5)
        ]);

        Transaction::factory()->create([
            'user_id' => $user2->id,
            'category_id' => $incomeCategory2->id,
            'amount' => 2000,
            'date' => now()->subDays(3)
        ]);

        Sanctum::actingAs($user1);

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200);

        $data = json_decode($response->getContent(), true);

        // Check that only user1's data is included
        $this->assertEquals(1000, $data['summary']['summary']['total_income']);
        $this->assertEquals(1, $data['summary']['transaction_count']);

        // Now check user2's data
        Sanctum::actingAs($user2);

        $response2 = $this->getJson('/api/dashboard');

        $data2 = json_decode($response2->getContent(), true);

        // Check that only user2's data is included
        $this->assertEquals(2000, $data2['summary']['summary']['total_income']);
        $this->assertEquals(1, $data2['summary']['transaction_count']);
    }
}
