<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use App\Models\Content;
use Tests\TestCase;

class CategoryContentTest extends TestCase
{
    public function test_can_add_content_to_category()
    {
        $category = Category::factory()->create();
        $content = Content::factory()->create();

        $response = $this->postJson("/api/categories/{$category->id}/contents", [
            'content_id' => $content->id
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('category_content', [
            'category_id' => $category->id,
            'content_id' => $content->id
        ]);
    }

    public function test_can_remove_content_from_category()
    {
        $category = Category::factory()->create();
        $content = Content::factory()->create();
        $category->contents()->attach($content->id);

        $response = $this->deleteJson("/api/categories/{$category->id}/contents/{$content->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('category_content', [
            'category_id' => $category->id,
            'content_id' => $content->id
        ]);
    }

    public function test_can_bulk_add_contents_to_category()
    {
        $category = Category::factory()->create();
        $contents = Content::factory()->count(3)->create();

        $response = $this->postJson("/api/categories/{$category->id}/contents/bulk", [
            'contents' => $contents->pluck('id')->toArray()
        ]);

        $response->assertStatus(201);
        foreach ($contents as $content) {
            $this->assertDatabaseHas('category_content', [
                'category_id' => $category->id,
                'content_id' => $content->id
            ]);
        }
    }

    public function test_can_bulk_remove_contents_from_category()
    {
        $category = Category::factory()->create();
        $contents = Content::factory()->count(3)->create();
        $category->contents()->attach($contents->pluck('id')->toArray());

        $response = $this->deleteJson("/api/categories/{$category->id}/contents/bulk", [
            'contents' => $contents->pluck('id')->toArray()
        ]);

        $response->assertStatus(204);
        foreach ($contents as $content) {
            $this->assertDatabaseMissing('category_content', [
                'category_id' => $category->id,
                'content_id' => $content->id
            ]);
        }
    }
}