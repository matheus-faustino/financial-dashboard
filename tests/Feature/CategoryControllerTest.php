<?php

namespace Tests\Feature;

use App\Enums\CategoryTypeEnum;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndex()
    {
        $user = User::factory()->create();
        $categories = Category::factory(3)->create(['user_id' => $user->id]);
        $systemCategory = Category::factory()->system()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200);
        // The number of categories should be 4 (3 user categories + 1 system category)
        $this->assertEquals(4, count(json_decode($response->getContent(), true)['categories']));
    }

    public function testStore()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $categoryData = [
            'name' => 'Test Category',
            'type' => CategoryTypeEnum::EXPENSE->value,
            'color' => '#FF5733'
        ];

        $response = $this->postJson('/api/categories', $categoryData);

        $response->assertStatus(201)
            ->assertJsonStructure(['category' => [
                'id',
                'name',
                'type',
                'color',
                'user_id',
                'is_system'
            ]]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
            'type' => CategoryTypeEnum::EXPENSE->value,
            'user_id' => $user->id,
            'is_system' => false
        ]);
    }

    public function testShow()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['category']);
    }

    public function testUpdate()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $updatedData = [
            'name' => 'Updated Category',
            'color' => '#33FF57'
        ];

        $response = $this->putJson("/api/categories/{$category->id}", $updatedData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
            'color' => '#33FF57'
        ]);
    }

    public function testDestroy()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Category deleted successfully']);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id
        ]);
    }

    public function testByType()
    {
        $user = User::factory()->create();
        $incomeCategory = Category::factory()->type(CategoryTypeEnum::INCOME)->create(['user_id' => $user->id]);
        $expenseCategory = Category::factory()->type(CategoryTypeEnum::EXPENSE)->create(['user_id' => $user->id]);
        $investmentCategory = Category::factory()->type(CategoryTypeEnum::INVESTMENT)->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/categories/by-type/" . CategoryTypeEnum::INCOME->value);

        $response->assertStatus(200);
        $categories = json_decode($response->getContent(), true)['data'];
        $this->assertEquals(1, count($categories));
        $this->assertEquals($incomeCategory->id, $categories[0]['id']);
    }

    public function testSystem()
    {
        $user = User::factory()->create();
        $userCategory = Category::factory()->create(['user_id' => $user->id]);
        $systemCategory = Category::factory()->system()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson("api/categories/system");

        $response->assertStatus(200);
        $categories = json_decode($response->getContent(), true)['categories'];
        $this->assertEquals(1, count($categories));
        $this->assertEquals($systemCategory->id, $categories[0]['id']);
    }

    public function testCustom()
    {
        $user = User::factory()->create();
        $userCategory = Category::factory()->create(['user_id' => $user->id]);
        $systemCategory = Category::factory()->system()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/categories/custom");

        $response->assertStatus(200);
        $categories = json_decode($response->getContent(), true)['categories'];
        $this->assertEquals(1, count($categories));
        $this->assertEquals($userCategory->id, $categories[0]['id']);
    }

    public function testMerge()
    {
        $user = User::factory()->create();
        $sourceCategory = Category::factory()->create(['user_id' => $user->id]);
        $targetCategory = Category::factory()->create(['user_id' => $user->id]);
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $sourceCategory->id
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/categories/merge", [
            'source_id' => $sourceCategory->id,
            'target_id' => $targetCategory->id
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Categories merged successfully']);

        // Source category should be deleted
        $this->assertDatabaseMissing('categories', [
            'id' => $sourceCategory->id
        ]);

        // Transaction should now be assigned to target category
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'category_id' => $targetCategory->id
        ]);
    }

    public function testCannotUpdateSystemCategory()
    {
        $user = User::factory()->create();
        $systemCategory = Category::factory()->system()->create();

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/categories/{$systemCategory->id}", [
            'name' => 'Updated Name'
        ]);

        $response->assertStatus(403);
    }

    public function testCannotDeleteSystemCategory()
    {
        $user = User::factory()->create();
        $systemCategory = Category::factory()->system()->create();

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/categories/{$systemCategory->id}");

        $response->assertStatus(403);
    }

    public function testAdminCanSeeAllCategories()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $userCategory = Category::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($admin);

        $response = $this->getJson("/api/categories/{$userCategory->id}");

        $response->assertStatus(200);
    }

    public function testCannotUpdateOtherUserCategory()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user2->id]);

        Sanctum::actingAs($user1);

        $response = $this->putJson("/api/categories/{$category->id}", [
            'name' => 'Updated Name'
        ]);

        $response->assertStatus(403);
    }

    public function testCannotMergeOtherUserCategories()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $sourceCategory = Category::factory()->create(['user_id' => $user2->id]);
        $targetCategory = Category::factory()->create(['user_id' => $user1->id]);

        Sanctum::actingAs($user1);

        $response = $this->postJson("/api/categories/merge", [
            'source_id' => $sourceCategory->id,
            'target_id' => $targetCategory->id
        ]);

        $response->assertStatus(403);
    }
}
