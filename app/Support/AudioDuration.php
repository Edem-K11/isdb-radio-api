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
     * Probe a remote file without downloading all of it: fetch the total size
     * plus the first chunk (which holds the MP3/AAC header, including the
     * VBR/Xing frame), then let getID3 compute the duration from the header
     * and the real total size.
     */
    public static function fromUrl(string $url): ?int
    {
        $tmp = tempnam(sys_get_temp_dir(), 'isdb_audio_');
        if ($tmp === false) {
            return null;
        }

        try {
            $totalSize = self::remoteSize($url);

            $chunk = Http::timeout(20)
                ->withHeaders(['Range' => 'bytes=0-1048575']) // first 1 MB
                ->get($url);

            if (! $chunk->successful() && $chunk->status() !== 206) {
                return self::fromFullDownload($url, $tmp);
            }

            file_put_contents($tmp, $chunk->body());

            $info = (new getID3())->analyze($tmp, $totalSize ?? 0);
            $seconds = $info['playtime_seconds'] ?? null;

            if ($seconds !== null) {
                return (int) round($seconds);
            }

            // Header-only probe was not enough (e.g. no Content-Length): fall
            // back to a full download with a generous timeout.
            return self::fromFullDownload($url, $tmp);
        } catch (Throwable) {
            return null;
        } finally {
            @unlink($tmp);
        }
    }

    private static function remoteSize(string $url): ?int
    {
        try {
            $head = Http::timeout(10)->head($url);
            $len = $head->header('Content-Length');

            return $len !== '' && $len !== null ? (int) $len : null;
        } catch (Throwable) {
            return null;
        }
    }

    private static function fromFullDownload(string $url, string $tmp): ?int
    {
        try {
            $response = Http::timeout(120)->get($url);
            if (! $response->successful()) {
                return null;
            }
            file_put_contents($tmp, $response->body());

            return self::fromFile($tmp);
        } catch (Throwable) {
            return null;
        }
    }
}
