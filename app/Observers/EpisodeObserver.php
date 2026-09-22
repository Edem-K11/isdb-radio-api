<?php

namespace App\Observers;

use App\Models\Episode;
use App\Support\AudioDuration;
use Illuminate\Support\Facades\Storage;

class EpisodeObserver
{
    /**
     * Fast path: read the duration straight from a freshly uploaded file, but
     * only when it's on the local/public disk — getID3 needs a real
     * filesystem path, which a remote disk (S3/R2) doesn't have. Remote-disk
     * uploads fall through to the deferred probe in saved() instead, the
     * same way an external URL already does, rather than downloading the
     * whole file into memory on every request.
     */
    public function saving(Episode $episode): void
    {
        if (filled($episode->duration_seconds) || blank($episode->audio_path)) {
            return;
        }

        $diskName = config('filesystems.default', 'public');
        if ($diskName === 's3') {
            return;
        }

        $disk = Storage::disk($diskName);
        if ($disk->exists($episode->audio_path)) {
            $episode->duration_seconds = AudioDuration::fromFile($disk->path($episode->audio_path));
        }
    }

    /**
     * Slow path: probe a remote URL (or a remote-disk upload's public URL)
     * after the response is sent, so saving the form stays instant even
     * without a queue worker.
     */
    public function saved(Episode $episode): void
    {
        if (filled($episode->duration_seconds)) {
            return;
        }

        $diskName = config('filesystems.default', 'public');
        $url = filled($episode->audio_url)
            ? $episode->audio_url
            : ($diskName === 's3' && filled($episode->audio_path)
                ? Storage::disk($diskName)->url($episode->audio_path)
                : null);

        if (blank($url)) {
            return;
        }

        $id = $episode->getKey();

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
