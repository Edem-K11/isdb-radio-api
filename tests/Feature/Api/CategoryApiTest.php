<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Episode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_categories_ordered_with_published_counts(): void
    {
        $b = Category::factory()->create(['name' => 'B', 'sort_order' => 2]);
        $a = Category::factory()->create(['name' => 'A', 'sort_order' => 1]);

        Episode::factory()->for($a)->count(2)->create();
        Episode::factory()->for($a)->draft()->create();

        $response = $this->getJson('/api/v1/categories')->assertOk();

        $this->assertSame(['A', 'B'], collect($response->json('data'))->pluck('name')->all());
        $response->assertJsonPath('data.0.episodes_count', 2)
            ->assertJsonPath('data.1.episodes_count', 0)
            ->assertJsonStructure(['data' => [['name', 'slug', 'color', 'sort_order', 'episodes_count']]]);
    }
}
