<?php

namespace App\Http\Controllers\Api;

use App\Enums\CategoryTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\MergeCategoriesRequest;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\Interfaces\CategoryServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryServiceInterface $categoryService)
    {
        $this->categoryService = $categoryService;

        $this->authorizeResource(Category::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $categories = $this->categoryService->getCategoriesForUser($request->user()->id);

        return response()->json(['categoriews' => $categories]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['is_system'] = false; // Ensure users can't create system categories

        $category = $this->categoryService->create($data);

        return response()->json(['category' => $category], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): JsonResponse
    {
        return response()->json(['category' => $category]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        // Don't allow updating is_system flag
        $data = collect($request->validated())->except(['is_system'])->toArray();

        $this->categoryService->update($data, $category->id);

        return response()->json(['category' => $category->fresh()]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): JsonResponse
    {
        $this->categoryService->delete($category->id);

        return response()->json(['message' => 'Category deleted successfully'], 200);
    }

    /**
     * Get categories by type
     */
    public function byType(Request $request, string $type): JsonResponse
    {
        $validTypes = [
            CategoryTypeEnum::INCOME->value,
            CategoryTypeEnum::EXPENSE->value,
            CategoryTypeEnum::INVESTMENT->value
        ];

        if (!in_array($type, $validTypes)) {
            return response()->json(['message' => 'Invalid category type'], 400);
        }

        $categories = $this->categoryService->getCategoriesByType(
            CategoryTypeEnum::from($type),
            $request->user()->id
        );

        return response()->json(['data' => $categories]);
    }

    /**
     * Get system categories
     */
    public function system(): JsonResponse
    {
        $categories = $this->categoryService->getSystemCategories();

        return response()->json(['categories' => $categories]);
    }

    /**
     * Get custom categories
     */
    public function custom(Request $request): JsonResponse
    {
        $categories = $this->categoryService->getCustomCategories($request->user()->id);

        return response()->json(['categories' => $categories]);
    }

    /**
     * Merge categories
     */
    public function merge(MergeCategoriesRequest $request): JsonResponse
    {
        $sourceId = $request->source_id;
        $targetId = $request->target_id;

        // Ensure both categories exist and belong to the user
        $sourceCategory = $this->categoryService->findById($sourceId);
        $targetCategory = $this->categoryService->findById($targetId);

        if (!$sourceCategory || !$targetCategory) {
            return response()->json(['message' => 'One or both categories not found'], 404);
        }

        // Only allow merging user's own categories
        $userId = $request->user()->id;

        if ($sourceCategory->user_id !== $userId || $targetCategory->user_id !== $userId) {
            return response()->json(['message' => 'You can only merge your own categories'], 403);
        }

        $success = $this->categoryService->mergeCategories($sourceId, $targetId);

        if ($success) {
            return response()->json(['message' => 'Categories merged successfully']);
        } else {
            return response()->json(['message' => 'Failed to merge categories'], 500);
        }
    }
}
