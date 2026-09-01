<?php

namespace Tests\Feature\Api;

use App\Models\StreamSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StreamApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_the_stream_configuration(): void
    {
        StreamSetting::query()->create([
            'station_name' => 'Radio ISDB',
            'stream_url' => 'https://example.com/live.mp3',
            'codec' => 'mp3',
            'is_on_air' => true,
        ]);

        $this->getJson('/api/v1/stream')
            ->assertOk()
            ->assertJsonPath('data.station_name', 'Radio ISDB')
            ->assertJsonPath('data.stream_url', 'https://example.com/live.mp3')
            ->assertJsonPath('data.is_on_air', true)
            ->assertJsonStructure([
                'data' => [
                    'station_name', 'slogan', 'stream_url', 'backup_url',
                    'codec', 'is_on_air', 'offline_message', 'logo_url', 'updated_at',
                ],
            ]);
    }

    public function test_it_creates_a_default_row_when_none_exists(): void
    {
        $this->getJson('/api/v1/stream')
            ->assertOk()
            ->assertJsonPath('data.station_name', 'Radio ISDB');

        $this->assertDatabaseCount('stream_settings', 1);
    }
}
