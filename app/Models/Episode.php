<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'audio_path',
        'audio_url',
        'audio_updated_at',
        'cover_path',
        'category_id',
        'duration_seconds',
        'plays_count',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'audio_updated_at' => 'datetime',
            'duration_seconds' => 'integer',
            'plays_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Episode $episode): void {
            if (blank($episode->slug)) {
                $episode->slug = Str::slug($episode->title).'-'.Str::lower(Str::random(6));
            }

            if ($episode->is_published && blank($episode->published_at)) {
                $episode->published_at = now();
            }

            // Bumped only when the actual audio changed — not on every edit —
            // so the app can tell a saved playback position still points at
            // the same content (see the `audio_updated_at` migration).
            if ($episode->isDirty(['audio_path', 'audio_url'])) {
                $episode->audio_updated_at = now();
            }
        });
    }

    /**
     * Publicly visible episodes only.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Absolute URL the app streams from: an uploaded file always wins over
     * the external URL field — a deliberate upload replacing a placeholder
     * link is the common case, and leaving both filled shouldn't silently
     * keep serving the old link. The external URL field is for episodes
     * genuinely hosted elsewhere (or files over the upload size limit) with
     * nothing uploaded here at all.
     */
    public function audioUrl(): ?string
    {
        if ($this->audio_path) {
            return Storage::disk(config('filesystems.default'))->url($this->audio_path);
        }

        return filled($this->audio_url) ? $this->audio_url : null;
    }

    public function coverUrl(): ?string
    {
        return $this->cover_path
            ? Storage::disk(config('filesystems.default'))->url($this->cover_path)
            : null;
    }
}
