<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppSettingResource;
use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;

class AppConfigController extends Controller
{
    /**
     * Non-stream app configuration (links, about text, min supported version).
     */
    public function show(): JsonResponse
    {
        return (new AppSettingResource(AppSetting::current()))
            ->response()
            ->setStatusCode(200);
    }
}
