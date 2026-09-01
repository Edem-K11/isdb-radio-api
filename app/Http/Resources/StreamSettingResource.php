<?php

namespace App\Http\Resources;

use App\Models\StreamSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StreamSetting
 */
class StreamSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'station_name' => $this->station_name,
            'slogan' => $this->slogan,
            'stream_url' => $this->stream_url,
            'backup_url' => $this->backup_url,
            'codec' => $this->codec,
            'is_on_air' => (bool) $this->is_on_air,
            'offline_message' => $this->offline_message,
            'logo_url' => $this->logoUrl(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
