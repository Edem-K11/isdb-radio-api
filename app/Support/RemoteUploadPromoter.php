<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Moves a just-uploaded file from the fast local 'public' disk to the
 * app's real default disk (R2/S3) after the response has already been
 * sent — so a Filament form save never has to wait on a slow upload to
 * remote storage.
 *
 * Why this exists: FileUpload fields save straight to whatever disk they're
 * configured with, synchronously, as part of the same request that saves
 * the record. Pointing that directly at R2 meant the browser sat waiting on
 * the admin's upload bandwidth to Cloudflare for the whole request — long
 * enough, for a several-MB audio file, that something in the path (Render's
 * own proxy, most likely) drops the connection before the response comes
 * back. The file finishes uploading server-side regardless (PHP keeps
 * running), which is exactly why it always did land in R2 correctly even
 * while the browser looked stuck forever: the browser was told nothing,
 * because nothing ever reached it.
 */
class RemoteUploadPromoter
{
    /**
     * @param  array<string>  $columns
     */
    public static function schedule(object $model, array $columns): void
    {
        if (config('filesystems.default') !== 's3') {
            return;
        }

        foreach ($columns as $column) {
            $path = $model->{$column} ?? null;

            if (blank($path) || (! Storage::disk('public')->exists($path))) {
                continue; // nothing local to promote (unchanged, or not a file field)
            }

            dispatch(function () use ($path): void {
                try {
                    $stream = Storage::disk('public')->readStream($path);

                    if (! is_resource($stream)) {
                        return;
                    }

                    try {
                        Storage::disk('s3')->writeStream($path, $stream, ['visibility' => 'public']);
                    } finally {
                        if (is_resource($stream)) {
                            fclose($stream);
                        }
                    }

                    Storage::disk('public')->delete($path);
                } catch (Throwable $e) {
                    // The file just stays on the local disk until the next
                    // successful save retries this — it'll be wiped on the
                    // next deploy, but nothing is lost right now, and it's
                    // still served locally in the meantime.
                    Log::warning("RemoteUploadPromoter: failed to promote [{$path}] to R2: {$e->getMessage()}");
                }
            })->afterResponse();
        }
    }
}
