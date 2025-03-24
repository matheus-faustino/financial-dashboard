<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tag\SearchTagRequest;
use App\Http\Requests\Tag\StoreTagRequest;
use App\Http\Requests\Tag\UpdateTagRequest;
use App\Models\Tag;
use App\Services\Interfaces\TagServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    protected $tagService;

    public function __construct(TagServiceInterface $tagService)
    {
        $this->tagService = $tagService;

        $this->authorizeResource(Tag::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $tags = $this->tagService->getTagsForUser($request->user()->id);

        return response()->json(['tags' => $tags]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTagRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $tag = $this->tagService->create($data);

        return response()->json(['tag' => $tag], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag): JsonResponse
    {
        return response()->json(['tag' => $tag]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTagRequest $request, Tag $tag): JsonResponse
    {
        $this->tagService->update($request->validated(), $tag->id);

        return response()->json(['tag' => $tag->fresh()]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag): JsonResponse
    {
        $this->tagService->delete($tag->id);

        return response()->json(['message' => 'Tag deleted successfully'], 200);
    }

    /**
     * Get tags sorted by frequency of use
     */
    public function byFrequency(Request $request): JsonResponse
    {
        $tags = $this->tagService->getTagsByFrequency($request->user()->id);

        return response()->json(['tags' => $tags]);
    }

    /**
     * Search tags by name
     */
    public function search(SearchTagRequest $request): JsonResponse
    {
        $tags = $this->tagService->searchTags(
            $request->query('query'),
            $request->user()->id
        );

        return response()->json(['tags' => $tags]);
    }
}
