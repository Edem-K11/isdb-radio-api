<?php

namespace App\Support;

use getID3;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Best-effort audio duration probing with getID3.
 */
class AudioDuration
{
    /** Read the duration (seconds) of a local file. */
    public static function fromFile(string $absolutePath): ?int
    {
        try {
            $info = (new getID3())->analyze($absolutePath);
            $seconds = $info['playtime_seconds'] ?? null;

            return $seconds !== null ? (int) round($seconds) : null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Download the file and let getID3 read its real duration.
     *
     * This used to probe just the first 1 MB and extrapolate from the total
     * Content-Length — fast, but only correct for constant-bitrate audio.
     * Most real-world MP3s (and anything with an ID3v2 tag carrying embedded
     * cover art ahead of the audio frames) are VBR, so that shortcut was
     * producing wrong durations. Downloading the whole file is the only way
     * to get it right; this runs once per episode, deferred and off the
     * request/response cycle, so the extra time doesn't matter.
     */
    public static function fromUrl(string $url): ?int
    {
        $tmp = tempnam(sys_get_temp_dir(), 'isdb_audio_');
        if ($tmp === false) {
            return null;
        }

        try {
            $response = Http::timeout(120)->get($url);
            if (! $response->successful()) {
                return null;
            }

            file_put_contents($tmp, $response->body());

            return self::fromFile($tmp);
        } catch (Throwable) {
            return null;
        } finally {
            @unlink($tmp);
        }
    }
}
