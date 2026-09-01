<?php

namespace App\Observers;

use App\Models\Episode;
use App\Support\AudioDuration;
use Illuminate\Support\Facades\Storage;

class EpisodeObserver
{
    /**
     * Fast path: read the duration straight from a freshly uploaded local file
     * (getID3 on a local file is instant).
     */
    public function saving(Episode $episode): void
    {
        if (filled($episode->duration_seconds)) {
            return;
        }

        if (filled($episode->audio_path)) {
            $disk = Storage::disk('public');
            if ($disk->exists($episode->audio_path)) {
                $episode->duration_seconds =
                    AudioDuration::fromFile($disk->path($episode->audio_path));
            }
        }
    }

    /**
     * Slow path: probe a remote URL after the response is sent, so saving the
     * form stays instant even without a queue worker.
     */
    public function saved(Episode $episode): void
    {
        if (filled($episode->duration_seconds) || blank($episode->audio_url)) {
            return;
        }

        $id = $episode->getKey();
        $url = $episode->audio_url;

        dispatch(function () use ($id, $url): void {
            $seconds = AudioDuration::fromUrl($url);
            if ($seconds !== null) {
                Episode::withoutEvents(
                    fn () => Episode::query()->whereKey($id)->update(['duration_seconds' => $seconds]),
                );
            }
        })->afterResponse();
    }
}
