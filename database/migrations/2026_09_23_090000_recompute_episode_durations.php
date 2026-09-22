<?php

use App\Models\Episode;
use App\Support\AudioDuration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * One-time fix: every duration_seconds already stored was computed by
     * the old partial-probe AudioDuration::fromUrl(), which guessed wrong
     * for VBR audio (see that class — it now downloads the whole file
     * instead). There's no shell on the Render free plan to run a one-off
     * command, so this migration is the mechanism: it runs exactly once,
     * automatically, on the next deploy.
     */
    public function up(): void
    {
        Episode::query()
            ->where(fn ($query) => $query->whereNotNull('audio_path')->orWhereNotNull('audio_url'))
            ->each(function (Episode $episode): void {
                $url = $episode->audioUrl();
                if (blank($url)) {
                    return;
                }

                try {
                    $seconds = AudioDuration::fromUrl($url);
                } catch (\Throwable $e) {
                    Log::warning("recompute_episode_durations: episode {$episode->id} failed: {$e->getMessage()}");

                    return;
                }

                if ($seconds !== null) {
                    Episode::withoutEvents(fn () => $episode->update(['duration_seconds' => $seconds]));
                }
            });
    }

    public function down(): void
    {
        // Not reversible — the old values were guesses, there's nothing to restore.
    }
};
