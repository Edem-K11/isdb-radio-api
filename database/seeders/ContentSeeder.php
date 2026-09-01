<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Episode;
use App\Support\AudioDuration;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            'Actualités', 'Éducation', 'Culture', 'Spiritualité', 'Musique', 'Débats',
        ])->map(fn (string $name, int $i) => Category::query()->updateOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name, 'color' => '#1B7A3A', 'sort_order' => $i],
        ));

        // Public-domain, ~5-7 min tracks so playback and the seek bar behave
        // like real episodes. Real durations are probed from the files below.
        $samples = [
            'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3',
            'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3',
            'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3',
            'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-5.mp3',
            'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-8.mp3',
        ];

        $durations = [];
        foreach ($samples as $url) {
            $durations[$url] = AudioDuration::fromUrl($url);
        }

        collect([
            'Le journal de la semaine',
            'Grand entretien : réussir ses études',
            'Chronique culturelle',
            'Méditation du dimanche',
            'Playlist étudiante',
            'Débat : intelligence artificielle et société',
            'Reportage : la vie sur le campus',
            'Portrait d\'un ancien élève',
        ])->each(function (string $title, int $i) use ($categories, $samples, $durations): void {
            $url = $samples[$i % count($samples)];
            Episode::query()->updateOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'title' => $title,
                    'description' => 'Émission enregistrée diffusée sur Radio ISDB.',
                    'audio_url' => $url,
                    'category_id' => $categories[$i % $categories->count()]->id,
                    'duration_seconds' => $durations[$url] ?? null,
                    'plays_count' => ($i * 53) % 400,
                    'is_published' => true,
                    'published_at' => now()->subDays($i * 3 + 1),
                ],
            );
        });

        // One unpublished + one future-dated episode to prove API scoping.
        Episode::query()->updateOrCreate(
            ['slug' => 'brouillon-non-publie'],
            [
                'title' => 'Brouillon non publié',
                'audio_url' => $samples[0],
                'duration_seconds' => $durations[$samples[0]] ?? null,
                'is_published' => false,
                'published_at' => null,
            ],
        );

        Episode::query()->updateOrCreate(
            ['slug' => 'emission-programmee'],
            [
                'title' => 'Émission programmée',
                'audio_url' => $samples[0],
                'duration_seconds' => $durations[$samples[0]] ?? null,
                'is_published' => true,
                'published_at' => now()->addWeek(),
            ],
        );
    }
}
