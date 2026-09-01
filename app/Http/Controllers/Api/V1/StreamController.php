<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\StreamSettingResource;
use App\Models\StreamSetting;
use Illuminate\Http\JsonResponse;

class StreamController extends Controller
{
    /**
     * Current live-stream configuration for the mobile app.
     */
    public function show(): JsonResponse
    {
        // Force 200 even on the first call that lazily creates the settings row
        // (a wrapped freshly-created model would otherwise yield 201).
        return (new StreamSettingResource(StreamSetting::current()))
            ->response()
            ->setStatusCode(200);
    }
}
