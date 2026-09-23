<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Episodes\Pages\EditEpisode;
use App\Models\Episode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class EpisodeEditFileFieldsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Reproduces the bug: once RemoteUploadPromoter has moved an episode's
     * files to R2, they no longer exist on the 'public' disk. Filament's
     * FileUpload::hydrateFiles() used to check `Storage::disk('public')->exists()`
     * and silently drop the path from the form's live state when it returned
     * false — even though the DB column and the actual file (on R2) are fine.
     * That emptied state then tripped requiredWithout('audio_url'), blocking
     * every edit of an already-promoted episode.
     */
    public function test_editing_an_episode_whose_files_were_already_promoted_to_r2_does_not_require_reuploading(): void
    {
        Storage::fake('public');

        $episode = Episode::factory()->create([
            'cover_path' => 'covers/existing-cover.jpg',
            'audio_path' => 'episodes/existing-audio.mp3',
            'audio_url' => null,
            'duration_seconds' => 120,
        ]);

        // Deliberately do NOT put the files on the fake 'public' disk — this
        // simulates the post-promotion state where they only exist on R2.
        $this->assertFalse(Storage::disk('public')->exists($episode->cover_path));
        $this->assertFalse(Storage::disk('public')->exists($episode->audio_path));

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $component = Livewire::test(EditEpisode::class, ['record' => $episode->getRouteKey()]);

        // The form should still see the existing paths in its live state,
        // not have silently dropped them.
        $component->assertSchemaStateSet([
            'cover_path' => $episode->cover_path,
            'audio_path' => $episode->audio_path,
        ]);

        $component
            ->call('save')
            ->assertHasNoFormErrors();

        $episode->refresh();

        $this->assertSame('covers/existing-cover.jpg', $episode->cover_path);
        $this->assertSame('episodes/existing-audio.mp3', $episode->audio_path);
    }
}
