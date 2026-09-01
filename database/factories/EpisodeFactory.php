<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Episode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Episode>
 */
class EpisodeFactory extends Factory
{
    protected $model = Episode::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'title' => rtrim($title, '.'),
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)),
            'description' => fake()->paragraph(),
            'audio_url' => 'https://download.samplelib.com/mp3/sample-15s.mp3',
            'audio_path' => null,
            'cover_path' => null,
            'category_id' => Category::factory(),
            'duration_seconds' => fake()->numberBetween(180, 3600),
            'plays_count' => fake()->numberBetween(0, 500),
            'is_published' => true,
            'published_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn () => [
            'is_published' => true,
            'published_at' => now()->addWeek(),
        ]);
    }
}
