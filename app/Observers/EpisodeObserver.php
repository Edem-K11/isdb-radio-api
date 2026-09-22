<?php

namespace App\Observers;

use App\Models\Episode;
use App\Support\AudioDuration;
use App\Support\RemoteUploadPromoter;
use Illuminate\Support\Facades\Storage;

class EpisodeObserver
{
    /**
     * Fast path: read the duration straight from the freshly uploaded file.
     * cover_path/audio_path always land on the local 'public' disk first,
     * regardless of the app's final storage disk (see saved() below and
     * RemoteUploadPromoter) — so a real local filesystem path for getID3 is
     * always available here, synchronously, even when R2 is the target.
     */
    public function saving(Episode $episode): void
    {
        if (filled($episode->duration_seconds) || blank($episode->audio_path)) {
            return;
        }

        $disk = Storage::disk('public');
        if ($disk->exists($episode->audio_path)) {
            $episode->duration_seconds = AudioDuration::fromFile($disk->path($episode->audio_path));
        }
    }

    /**
     * Promotes any freshly uploaded file to the real storage disk (R2) off
     * the request/response cycle, and — for the one remaining case saving()
     * can't handle synchronously, an external audio_url — probes the
     * duration the slow way, also deferred.
     */
    public function saved(Episode $episode): void
    {
        RemoteUploadPromoter::schedule($episode, ['cover_path', 'audio_path']);

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
