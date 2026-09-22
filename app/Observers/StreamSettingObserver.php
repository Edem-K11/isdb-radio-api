<?php

namespace App\Observers;

use App\Models\StreamSetting;
use App\Support\RemoteUploadPromoter;

class StreamSettingObserver
{
    /**
     * logo_path always saves to the local 'public' disk first (see
     * StreamSettingForm) — promote it to R2 off the request/response cycle,
     * same as episode covers and audio.
     */
    public function saved(StreamSetting $setting): void
    {
        RemoteUploadPromoter::schedule($setting, ['logo_path']);
    }
}
