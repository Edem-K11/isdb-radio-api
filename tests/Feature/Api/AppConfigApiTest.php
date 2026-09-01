<?php

namespace Tests\Feature\Api;

use App\Models\AppSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppConfigApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_app_configuration(): void
    {
        AppSetting::query()->create([
            'about_text' => 'A propos de Radio ISDB',
            'website_url' => 'https://isdb.example',
            'tiktok_url' => 'https://tiktok.com/@isdb',
            'min_supported_version' => '1.2.0',
        ]);

        $this->getJson('/api/v1/app-config')
            ->assertOk()
            ->assertJsonPath('data.min_supported_version', '1.2.0')
            ->assertJsonPath('data.website_url', 'https://isdb.example')
            ->assertJsonPath('data.tiktok_url', 'https://tiktok.com/@isdb')
            ->assertJsonStructure([
                'data' => [
                    'about_text', 'website_url', 'facebook_url', 'instagram_url', 'youtube_url',
                    'tiktok_url', 'contact_phone', 'contact_email', 'privacy_policy_url',
                    'android_store_url', 'min_supported_version',
                ],
            ]);
    }
}
