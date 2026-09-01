<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\StreamSetting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        StreamSetting::query()->updateOrCreate([], [
            'station_name' => 'Radio ISDB',
            'slogan' => 'La radio de l\'Institut Superieur Don Bosco',
            'stream_url' => 'https://jazzradio.ice.infomaniak.ch/jazzradio-high.mp3',
            'backup_url' => null,
            'codec' => 'mp3',
            'is_on_air' => true,
            'offline_message' => 'La radio est actuellement hors antenne. Revenez bientot !',
        ]);

        AppSetting::query()->updateOrCreate([], [
            'about_text' => "Radio ISDB est la webradio de l'Institut Superieur Don Bosco. "
                ."Retrouvez le direct et les emissions enregistrees.",
            'website_url' => 'https://www.isdb.example',
            'facebook_url' => null,
            'instagram_url' => null,
            'youtube_url' => null,
            'contact_email' => 'radio@isdb.example',
            'privacy_policy_url' => 'https://www.isdb.example/confidentialite',
            'min_supported_version' => '1.0.0',
        ]);
    }
}
