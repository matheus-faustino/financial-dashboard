<?php

namespace Tests\Unit\Services;

use App\Enums\CategoryTypeEnum;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\CategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CategoryService $categoryService;

    public function setUp(): void
    {
        parent::setUp();

        $this->categoryService = app(CategoryService::class);
    }

    public function testCanGetCategoriesForUser()
    {
        $user = User::factory()->create();
        $systemCategory = Category::factory()->system()->create();
        $userCategories = Category::factory()->count(3)->create(['user_id' => $user->id]);

        $anotherUser = User::factory()->create();
        Category::factory()->count(2)->create(['user_id' => $anotherUser->id]);

        $categories = $this->categoryService->getCategoriesForUser($user->id);

        $this->assertCount(4, $categories);
        $this->assertTrue($categories->contains('id', $systemCategory->id));

        foreach ($userCategories as $category) {
            $this->assertTrue($categories->contains('id', $category->id));
        }
    }

    public function testCanGetCategoriesByType()
    {
        $user = User::factory()->create();

        $expenseCategories = Category::factory()
            ->count(3)
            ->type(CategoryTypeEnum::EXPENSE)
            ->create(['user_id' => $user->id]);

        Category::factory()
            ->count(2)
            ->type(CategoryTypeEnum::INCOME)
            ->create(['user_id' => $user->id]);

        Category::factory()
            ->count(1)
            ->type(CategoryTypeEnum::INVESTMENT)
            ->create(['user_id' => $user->id]);

        $categories = $this->categoryService->getCategoriesByType(CategoryTypeEnum::EXPENSE, $user->id);
        $this->assertCount(3, $categories);

        foreach ($expenseCategories as $category) {
            $this->assertTrue($categories->contains('id', $category->id));
        }
    }

    public function testCanGetSystemCategories()
    {
        $systemCategories = Category::factory()->system()->count(3)->create();
        Category::factory()->count(2)->create();

        $categories = $this->categoryService->getSystemCategories();

        $this->assertCount(3, $categories);
        foreach ($systemCategories as $category) {
            $this->assertTrue($categories->contains('id', $category->id));
        }
    }

    public function testCanGetCustomCategories()
    {
        $user = User::factory()->create();
        Category::factory()->system()->count(2)->create();
        $userCategories = Category::factory()->count(3)->create(['user_id' => $user->id]);

        $categories = $this->categoryService->getCustomCategories($user->id);

        $this->assertCount(3, $categories);
        foreach ($userCategories as $category) {
            $this->assertTrue($categories->contains('id', $category->id));
        }
    }

    public function testCanGetCategoryWithTransactions()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        Transaction::factory()->count(3)->create([
            'user_id' => $user->id,
            'category_id' => $category->id
        ]);

        $categoryWithTransactions = $this->categoryService->getCategoryWithTransactions($category->id);

        $this->assertNotNull($categoryWithTransactions);
        $this->assertEquals($category->id, $categoryWithTransactions->id);
        $this->assertCount(3, $categoryWithTransactions->transactions);
    }

    public function testCanMergeCategories()
    {
        $user = User::factory()->create();

        $sourceCategory = Category::factory()->create(['user_id' => $user->id]);
        $targetCategory = Category::factory()->create(['user_id' => $user->id]);

        Transaction::factory()->count(5)->create([
            'user_id' => $user->id,
            'category_id' => $sourceCategory->id
        ]);

        $result = $this->categoryService->mergeCategories($sourceCategory->id, $targetCategory->id);

        $this->assertTrue($result);

        $this->assertNull(Category::find($sourceCategory->id));

        $this->assertEquals(5, Transaction::where('category_id', $targetCategory->id)->count());
    }

    public function testCannotMergeSystemCategory()
    {
        $systemCategory = Category::factory()->system()->create();
        $userCategory = Category::factory()->create();

        $result = $this->categoryService->mergeCategories($systemCategory->id, $userCategory->id);

        $this->assertFalse($result);

        $this->assertNotNull(Category::find($systemCategory->id));
    }

    public function testCanCreateCategory()
    {
        $user = User::factory()->create();

        $categoryData = [
            'name' => 'New Test Category',
            'type' => CategoryTypeEnum::EXPENSE->value,
            'color' => '#FF5733',
            'user_id' => $user->id
        ];

        $category = $this->categoryService->create($categoryData);

        $this->assertNotNull($category);
        $this->assertEquals('New Test Category', $category->name);
        $this->assertEquals(CategoryTypeEnum::EXPENSE, $category->type);
        $this->assertEquals('#FF5733', $category->color);
        $this->assertEquals($user->id, $category->user_id);
    }

    public function testCanUpdateCategory()
    {
        $category = Category::factory()->create([
            'name' => 'Original Category Name',
            'color' => '#000000'
        ]);

        $updateData = [
            'name' => 'Updated Category Name',
            'color' => '#FFFFFF'
        ];

        $result = $this->categoryService->update($updateData, $category->id);

        $this->assertTrue($result);

        $updatedCategory = Category::find($category->id);
        $this->assertEquals('Updated Category Name', $updatedCategory->name);
        $this->assertEquals('#FFFFFF', $updatedCategory->color);
    }

    public function testCanDeleteCategory()
    {
        $category = Category::factory()->create();

        $result = $this->categoryService->delete($category->id);

        $this->assertTrue($result);
        $this->assertNull(Category::find($category->id));
    }
}
