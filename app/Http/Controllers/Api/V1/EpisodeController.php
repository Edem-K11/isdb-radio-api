<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EpisodeResource;
use App\Models\Episode;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

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
            'sort' => ['sometimes', Rule::in(['latest', 'oldest', 'plays', 'longest', 'shortest'])],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
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
                function ($query, string $term) {
                    // Case-insensitive on every driver we run on (sqlite in
                    // dev, pgsql in prod): plain `like` is case-*sensitive*
                    // on Postgres, so "reportage" silently missed a title
                    // stored as "Reportage" — LOWER() on both sides fixes
                    // that everywhere instead of only in dev/sqlite.
                    $like = '%'.mb_strtolower($term, 'UTF-8').'%';

                    return $query->where(
                        fn ($q) => $q->whereRaw('LOWER(title) LIKE ?', [$like])
                            ->orWhereRaw('LOWER(description) LIKE ?', [$like])
                            ->orWhereHas(
                                'category',
                                fn ($cat) => $cat->whereRaw('LOWER(name) LIKE ?', [$like]),
                            ),
                    );
                },
            )
            ->when(
                $validated['date_from'] ?? null,
                // whereDate compares the date part only, so date_to stays
                // inclusive of the whole day regardless of published_at's
                // time-of-day component.
                fn ($query, string $date) => $query->whereDate('published_at', '>=', $date),
            )
            ->when(
                $validated['date_to'] ?? null,
                fn ($query, string $date) => $query->whereDate('published_at', '<=', $date),
            )
            ->when(
                $validated['sort'] ?? 'latest',
                fn ($query, string $sort) => match ($sort) {
                    'oldest' => $query->orderBy('published_at'),
                    'plays' => $query->orderByDesc('plays_count'),
                    'longest' => $query->orderByDesc('duration_seconds'),
                    'shortest' => $query->orderBy('duration_seconds'),
                    default => $query->orderByDesc('published_at'),
                },
            )
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
