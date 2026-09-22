<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Episode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EpisodeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_only_published_episodes_newest_first(): void
    {
        $old = Episode::factory()->create(['published_at' => now()->subMonth()]);
        $new = Episode::factory()->create(['published_at' => now()->subDay()]);
        Episode::factory()->draft()->create();
        Episode::factory()->scheduled()->create();

        $response = $this->getJson('/api/v1/episodes')->assertOk();

        $slugs = collect($response->json('data'))->pluck('slug');

        $this->assertSame([$new->slug, $old->slug], $slugs->all());
        $response->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [['title', 'slug', 'audio_url', 'duration_seconds', 'published_at', 'category']],
                'links',
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_it_filters_by_category_slug(): void
    {
        $news = Category::factory()->create(['slug' => 'actualites']);
        $music = Category::factory()->create(['slug' => 'musique']);
        Episode::factory()->for($news)->create();
        Episode::factory()->for($music)->create();

        $this->getJson('/api/v1/episodes?category=actualites')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.category.slug', 'actualites');
    }

    public function test_it_searches_by_title(): void
    {
        Episode::factory()->create(['title' => 'Le journal du campus']);
        Episode::factory()->create(['title' => 'Playlist etudiante']);

        $this->getJson('/api/v1/episodes?search=journal')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Le journal du campus');
    }

    public function test_search_is_case_insensitive(): void
    {
        Episode::factory()->create(['title' => 'Reportage : la vie sur le campus']);

        $this->getJson('/api/v1/episodes?search=reportage')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_it_searches_by_category_name(): void
    {
        $music = Category::factory()->create(['name' => 'Musique']);
        Episode::factory()->for($music)->create(['title' => 'Playlist etudiante']);
        Episode::factory()->create(['title' => 'Le journal du campus']);

        $this->getJson('/api/v1/episodes?search=musique')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Playlist etudiante');
    }

    public function test_it_caps_per_page(): void
    {
        $this->getJson('/api/v1/episodes?per_page=999')->assertStatus(422);
    }

    public function test_it_sorts_by_most_played(): void
    {
        $quiet = Episode::factory()->create(['plays_count' => 3]);
        $popular = Episode::factory()->create(['plays_count' => 300]);

        $this->getJson('/api/v1/episodes?sort=plays')
            ->assertOk()
            ->assertJsonPath('data.0.slug', $popular->slug)
            ->assertJsonPath('data.1.slug', $quiet->slug);
    }

    public function test_it_sorts_by_longest(): void
    {
        $short = Episode::factory()->create(['duration_seconds' => 120]);
        $long = Episode::factory()->create(['duration_seconds' => 3000]);

        $this->getJson('/api/v1/episodes?sort=longest')
            ->assertOk()
            ->assertJsonPath('data.0.slug', $long->slug)
            ->assertJsonPath('data.1.slug', $short->slug);
    }

    public function test_it_sorts_by_shortest(): void
    {
        $short = Episode::factory()->create(['duration_seconds' => 120]);
        $long = Episode::factory()->create(['duration_seconds' => 3000]);

        $this->getJson('/api/v1/episodes?sort=shortest')
            ->assertOk()
            ->assertJsonPath('data.0.slug', $short->slug)
            ->assertJsonPath('data.1.slug', $long->slug);
    }

    public function test_it_rejects_an_unknown_sort_value(): void
    {
        $this->getJson('/api/v1/episodes?sort=bogus')->assertStatus(422);
    }

    public function test_it_shows_a_single_published_episode(): void
    {
        $episode = Episode::factory()->create();

        $this->getJson("/api/v1/episodes/{$episode->slug}")
            ->assertOk()
            ->assertJsonPath('data.slug', $episode->slug);
    }

    public function test_it_hides_unpublished_episode_detail(): void
    {
        $episode = Episode::factory()->draft()->create();

        $this->getJson("/api/v1/episodes/{$episode->slug}")->assertNotFound();
    }

    public function test_it_increments_the_play_counter(): void
    {
        $episode = Episode::factory()->create(['plays_count' => 4]);

        $this->postJson("/api/v1/episodes/{$episode->slug}/play")->assertNoContent();

        $this->assertSame(5, $episode->fresh()->plays_count);
    }
}
