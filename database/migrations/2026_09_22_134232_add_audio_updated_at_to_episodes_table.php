<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A dedicated timestamp for just the audio (file or URL) — bumped only
     * when that actually changes, unlike `updated_at` which moves on every
     * edit (title, category…). The app uses it to tell whether a saved
     * "resume from here" position still points at the same audio, so an
     * admin swapping a file doesn't leave a listener resuming into
     * unrelated content.
     */
    public function up(): void
    {
        Schema::table('episodes', function (Blueprint $table) {
            $table->timestamp('audio_updated_at')->nullable()->after('audio_url');
        });

        // Existing rows: seed with updated_at so they have *some* baseline
        // instead of null (which the app treats as "unknown, trust it").
        DB::table('episodes')->update(['audio_updated_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('episodes', function (Blueprint $table) {
            $table->dropColumn('audio_updated_at');
        });
    }
};
