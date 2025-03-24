<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndex()
    {
        $user = User::factory()->create();
        $tags = Tag::factory(3)->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/tags');

        $response->assertStatus(200)
            ->assertJsonStructure(['tags']);
    }

    public function testStore()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $tagData = [
            'name' => 'New Tag'
        ];

        $response = $this->postJson('/api/tags', $tagData);

        $response->assertStatus(201)
            ->assertJsonStructure(['tag' => [
                'id',
                'name',
                'user_id'
            ]]);

        $this->assertDatabaseHas('tags', [
            'name' => 'New Tag',
            'user_id' => $user->id
        ]);
    }

    public function testShow()
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/tags/{$tag->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['tag']);
    }

    public function testUpdate()
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $updatedData = [
            'name' => 'Updated Tag'
        ];

        $response = $this->putJson("/api/tags/{$tag->id}", $updatedData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Updated Tag'
        ]);
    }

    public function testDestroy()
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/tags/{$tag->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Tag deleted successfully']);

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id
        ]);
    }

    public function testByFrequency()
    {
        $user = User::factory()->create();
        $tag1 = Tag::factory()->create(['user_id' => $user->id]);
        $tag2 = Tag::factory()->create(['user_id' => $user->id]);

        // Create transactions and attach tags
        $transaction1 = Transaction::factory()->create(['user_id' => $user->id]);
        $transaction2 = Transaction::factory()->create(['user_id' => $user->id]);
        $transaction3 = Transaction::factory()->create(['user_id' => $user->id]);

        // Tag1 is used twice, Tag2 is used once
        $transaction1->tags()->attach($tag1->id);
        $transaction2->tags()->attach($tag1->id);
        $transaction3->tags()->attach($tag2->id);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/tags/by-frequency');

        $response->assertStatus(200)
            ->assertJsonStructure(['tags']);

        $tags = json_decode($response->getContent(), true)['tags'];

        // Check tag1 comes before tag2 (more frequent first)
        $tag1Index = array_search($tag1->id, array_column($tags, 'id'));
        $tag2Index = array_search($tag2->id, array_column($tags, 'id'));
        $this->assertLessThan($tag2Index, $tag1Index);
    }

    public function testSearch()
    {
        $user = User::factory()->create();
        $tag1 = Tag::factory()->create(['name' => 'groceries', 'user_id' => $user->id]);
        $tag2 = Tag::factory()->create(['name' => 'entertainment', 'user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/tags/search?query=groc');

        $response->assertStatus(200)
            ->assertJsonStructure(['tags']);
    }

    public function testCannotViewOtherUserTag()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $tag = Tag::factory()->create(['user_id' => $user2->id]);

        Sanctum::actingAs($user1);

        $response = $this->getJson("/api/tags/{$tag->id}");

        $response->assertStatus(403);
    }

    public function testCannotUpdateOtherUserTag()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $tag = Tag::factory()->create(['user_id' => $user2->id]);

        Sanctum::actingAs($user1);

        $response = $this->putJson("/api/tags/{$tag->id}", [
            'name' => 'Updated Name'
        ]);

        $response->assertStatus(403);
    }

    public function testCannotDeleteOtherUserTag()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $tag = Tag::factory()->create(['user_id' => $user2->id]);

        Sanctum::actingAs($user1);

        $response = $this->deleteJson("/api/tags/{$tag->id}");

        $response->assertStatus(403);
    }

    public function testAdminCanManageAnyTag()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $tag = Tag::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($admin);

        // Admin can view tag
        $viewResponse = $this->getJson("/api/tags/{$tag->id}");
        $viewResponse->assertStatus(200);

        // Admin can update tag
        $updateResponse = $this->putJson("/api/tags/{$tag->id}", [
            'name' => 'Admin Updated'
        ]);
        $updateResponse->assertStatus(200);

        // Admin can delete tag
        $deleteResponse = $this->deleteJson("/api/tags/{$tag->id}");
        $deleteResponse->assertStatus(200);
    }
}
