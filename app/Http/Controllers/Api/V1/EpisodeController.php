<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EpisodeResource;
use App\Models\Episode;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class EpisodeController extends Controller
{
    /**
     * Paginated list of published episodes, newest first.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'category' => ['sometimes', 'string', 'max:120'],
            'search' => ['sometimes', 'string', 'max:120'],
        ]);

        $episodes = Episode::query()
            ->published()
            ->with('category')
            ->when(
                $validated['category'] ?? null,
                fn ($query, string $slug) => $query->whereHas(
                    'category',
                    fn ($q) => $q->where('slug', $slug),
                ),
            )
            ->when(
                $validated['search'] ?? null,
                fn ($query, string $term) => $query->where(
                    fn ($q) => $q->where('title', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%"),
                ),
            )
            ->orderByDesc('published_at')
            ->paginate($validated['per_page'] ?? 15)
            ->withQueryString();

        return EpisodeResource::collection($episodes);
    }

    /**
     * Single published episode by slug.
     */
    public function show(string $slug): EpisodeResource
    {
        $episode = Episode::query()
            ->published()
            ->with('category')
            ->where('slug', $slug)
            ->firstOrFail();

        return new EpisodeResource($episode);
    }

    /**
     * Fire-and-forget play counter. Returns 204 on success.
     */
    public function play(string $slug): Response
    {
        Episode::query()->published()->where('slug', $slug)->firstOrFail()->increment('plays_count');

        return response()->noContent();
    }
}
