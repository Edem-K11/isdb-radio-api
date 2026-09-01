<?php

namespace App\Http\Resources;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AppSetting
 */
class AppSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'about_text' => $this->about_text,
            'website_url' => $this->website_url,
            'facebook_url' => $this->facebook_url,
            'instagram_url' => $this->instagram_url,
            'youtube_url' => $this->youtube_url,
            'tiktok_url' => $this->tiktok_url,
            'contact_phone' => $this->contact_phone,
            'contact_email' => $this->contact_email,
            'privacy_policy_url' => $this->privacy_policy_url,
            'android_store_url' => $this->android_store_url,
            'min_supported_version' => $this->min_supported_version,
        ];
    }
}
