<?php

namespace App\Models;

use App\Models\Concerns\IsSingleton;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Single row holding the live-stream configuration served to the mobile app.
 */
class StreamSetting extends Model
{
    use IsSingleton;

    protected $fillable = [
        'station_name',
        'slogan',
        'stream_url',
        'backup_url',
        'codec',
        'is_on_air',
        'offline_message',
        'logo_path',
    ];

    protected $attributes = [
        'station_name' => 'Radio ISDB',
        'stream_url' => 'https://jazzradio.ice.infomaniak.ch/jazzradio-high.mp3',
        'codec' => 'mp3',
        'is_on_air' => true,
    ];

    protected function casts(): array
    {
        return [
            'is_on_air' => 'boolean',
        ];
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }
}
