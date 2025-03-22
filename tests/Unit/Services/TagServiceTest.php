<?php

namespace Tests\Unit\Services;

use App\Models\Tag;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TagService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class TagServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TagService $tagService;

    public function setUp(): void
    {
        parent::setUp();

        $this->tagService = app(TagService::class);
    }

    public function testCanGetTagsForUser()
    {
        $user = User::factory()->create();
        $userTags = Tag::factory()->count(3)->create(['user_id' => $user->id]);

        $anotherUser = User::factory()->create();
        Tag::factory()->count(2)->create(['user_id' => $anotherUser->id]);

        $tags = $this->tagService->getTagsForUser($user->id);

        $this->assertCount(3, $tags);

        foreach ($userTags as $tag) {
            $this->assertTrue($tags->contains('id', $tag->id));
        }
    }

    public function testCanGetTagsByFrequency()
    {
        $user = User::factory()->create();

        $tag1 = Tag::factory()->create(['user_id' => $user->id]);
        $tag2 = Tag::factory()->create(['user_id' => $user->id]);
        $tag3 = Tag::factory()->create(['user_id' => $user->id]);

        $transactions = Transaction::factory()->count(5)->create(['user_id' => $user->id]);

        $transactions[0]->tags()->attach($tag1->id);
        $transactions[1]->tags()->attach($tag1->id);
        $transactions[2]->tags()->attach($tag1->id);
        $transactions[3]->tags()->attach($tag2->id);

        $tags = $this->tagService->getTagsByFrequency($user->id);

        $this->assertEquals($tag1->id, $tags->first()->id);
        $this->assertEquals($tag2->id, $tags[1]->id);
        $this->assertEquals($tag3->id, $tags->last()->id);
    }

    public function testCanSearchTags()
    {
        $user = User::factory()->create();

        Tag::factory()->create([
            'user_id' => $user->id,
            'name' => 'Groceries'
        ]);

        Tag::factory()->create([
            'user_id' => $user->id,
            'name' => 'Restaurant'
        ]);

        Tag::factory()->create([
            'user_id' => $user->id,
            'name' => 'Food Delivery'
        ]);

        // Search for tags containing "cer"
        $tags = $this->tagService->searchTags('cer', $user->id);

        $this->assertCount(1, $tags);
        $this->assertEquals('Groceries', $tags->first()->name);

        // Search for tags containing "o"
        $tags = $this->tagService->searchTags('o', $user->id);

        $this->assertCount(2, $tags);
        $this->assertNotContains('Restaurant', $tags->pluck('name')->toArray());
        $this->assertContains('Food Delivery', $tags->pluck('name')->toArray());
    }

    public function testCanGetTagsWithTransactionCount()
    {
        $user = User::factory()->create();

        $tag1 = Tag::factory()->create(['user_id' => $user->id]);
        $tag2 = Tag::factory()->create(['user_id' => $user->id]);

        $transactions = Transaction::factory()->count(4)->create(['user_id' => $user->id]);

        $transactions[0]->tags()->attach($tag1->id);
        $transactions[1]->tags()->attach($tag1->id);
        $transactions[2]->tags()->attach($tag1->id);
        $transactions[3]->tags()->attach($tag2->id);

        $tags = $this->tagService->getTagsWithTransactionCount($user->id);

        $this->assertCount(2, $tags);

        $databaseTag1 = $tags->where('id', $tag1->id)->first();
        $databaseTag2 = $tags->where('id', $tag2->id)->first();

        $this->assertEquals(3, $databaseTag1->transactions_count);
        $this->assertEquals(1, $databaseTag2->transactions_count);
    }

    public function testCanCreateMultipleTags()
    {
        $user = User::factory()->create();

        // Create one tag that already exists
        Tag::factory()->create([
            'user_id' => $user->id,
            'name' => 'Existing Tag'
        ]);

        // Create multiple tags including duplicates and the existing one
        $tagNames = ['New Tag 1', 'New Tag 2', 'Existing Tag', 'New Tag 1'];

        $tags = $this->tagService->createMultipleTags($tagNames, $user->id);

        $this->assertCount(3, $tags);

        $tagNames = $tags->pluck('name')->toArray();
        $this->assertContains('Existing Tag', $tagNames);
        $this->assertContains('New Tag 1', $tagNames);
        $this->assertContains('New Tag 2', $tagNames);

        $userTags = $this->tagService->findWhere(['user_id' => $user->id]);

        $this->assertEquals(3, $userTags->count());
    }

    public function testCreateMultipleTagsWithEmptyNames()
    {
        $user = User::factory()->create();

        $tagNames = ['Valid Tag', '', '  ', 'Another Tag'];

        $tags = $this->tagService->createMultipleTags($tagNames, $user->id);

        $this->assertCount(2, $tags);

        $tagNames = $tags->pluck('name')->toArray();
        $this->assertContains('Valid Tag', $tagNames);
        $this->assertContains('Another Tag', $tagNames);
    }

    public function testCanCreateTag()
    {
        $user = User::factory()->create();

        $tagData = [
            'name' => 'New Test Tag',
            'user_id' => $user->id
        ];

        $tag = $this->tagService->create($tagData);

        $this->assertNotNull($tag);
        $this->assertEquals('New Test Tag', $tag->name);
        $this->assertEquals($user->id, $tag->user_id);
    }

    public function testCanUpdateTag()
    {
        $tag = Tag::factory()->create([
            'name' => 'Original Tag Name'
        ]);

        $updateData = [
            'name' => 'Updated Tag Name'
        ];

        $result = $this->tagService->update($updateData, $tag->id);

        $this->assertTrue($result);

        $tag->refresh();
        $this->assertEquals('Updated Tag Name', $tag->name);
    }

    public function testCanDeleteTag()
    {
        $tag = Tag::factory()->create();

        $result = $this->tagService->delete($tag->id);

        $this->assertTrue($result);
        $this->assertNull(Tag::find($tag->id));
    }
}
