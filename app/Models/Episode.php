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
     * Absolute URL the app streams from: external URL wins, otherwise the
     * uploaded file on the public disk.
     */
    public function audioUrl(): ?string
    {
        if (filled($this->audio_url)) {
            return $this->audio_url;
        }

        return $this->audio_path ? Storage::disk('public')->url($this->audio_path) : null;
    }

    public function coverUrl(): ?string
    {
        return $this->cover_path ? Storage::disk('public')->url($this->cover_path) : null;
    }
}
