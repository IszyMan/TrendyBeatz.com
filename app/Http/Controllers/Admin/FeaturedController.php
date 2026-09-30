<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FeaturedController extends Controller
{
    private function kind(Request $request): string
    {
        abort_unless(
            $request->user()
                && (int) $request->user()->roleid
                    === (int) config('admin.administrator'),
            403
        );

        $kind = $request->route('kind');

        abort_unless(in_array($kind, ['song', 'album'], true), 404);

        return $kind;
    }

    private function routePrefix(string $kind): string
    {
        return $kind === 'song'
            ? 'admin.featured-songs'
            : 'admin.featured-albums';
    }

    public function index(Request $request)
    {
        $kind = $this->kind($request);
        $prefix = $this->routePrefix($kind);

        if ($kind === 'song') {
            $features = DB::table('listing_features')
                ->select('listing_id')
                ->selectRaw('MAX(id) as latest_feature_id')
                ->selectRaw("
                    MAX(
                        CASE WHEN listing_feature_type = 1
                        THEN 1 ELSE 0 END
                    ) as song_day
                ")
                ->selectRaw("
                    MAX(
                        CASE WHEN listing_feature_type = 2
                        THEN 1 ELSE 0 END
                    ) as song_week
                ")
                ->whereIn('listing_feature_type', [1, 2])
                ->groupBy('listing_id');

            $items = DB::table('listings as item')
                ->joinSub(
                    $features,
                    'featured',
                    'featured.listing_id',
                    '=',
                    'item.id'
                )
                ->leftJoin(
                    'artists as artist',
                    'artist.id',
                    '=',
                    'item.artist_id'
                )
                ->select(
                    'item.id',
                    'item.track_title as title',
                    'item.is_published',
                    'featured.song_day',
                    'featured.song_week'
                )
                ->selectRaw("
                    COALESCE(
                        NULLIF(artist.stage_name, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
                ->orderByDesc('featured.latest_feature_id')
                ->orderByDesc('item.id')
                ->paginate(20);
        } else {
            $features = DB::table('album_populars')
                ->select('album_id')
                ->selectRaw('MAX(id) as latest_feature_id')
                ->selectRaw(
                    'MAX(CAST(rate_no AS UNSIGNED)) as rate_no'
                )
                ->groupBy('album_id');

            $items = DB::table('albums as item')
                ->joinSub(
                    $features,
                    'featured',
                    'featured.album_id',
                    '=',
                    'item.id'
                )
                ->leftJoin(
                    'artists as artist',
                    'artist.id',
                    '=',
                    'item.artist_id'
                )
                ->select(
                    'item.id',
                    'item.title',
                    'item.is_published',
                    'featured.rate_no'
                )
                ->selectRaw("
                    COALESCE(
                        NULLIF(artist.stage_name, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
                ->orderByDesc('featured.latest_feature_id')
                ->orderByDesc('item.id')
                ->paginate(20);
        }

        return view(
            'admin.featured.index',
            compact('kind', 'prefix', 'items')
        );
    }

    public function create(Request $request)
    {
        return $this->form($request);
    }

    public function edit(Request $request, int $id)
    {
        return $this->form($request, $id);
    }

    private function form(Request $request, ?int $id = null)
    {
        $kind = $this->kind($request);
        $prefix = $this->routePrefix($kind);

        if ($id === null) {
            $data = $request->validate([
                'id' => ['nullable', 'integer', 'min:1'],
            ]);

            $id = isset($data['id']) ? (int) $data['id'] : null;
        }

        $item = null;
        $songDay = false;
        $songWeek = false;

        if ($id !== null) {
            $table = $kind === 'song' ? 'listings' : 'albums';

            $item = DB::table($table . ' as item')
                ->leftJoin(
                    'artists as artist',
                    'artist.id',
                    '=',
                    'item.artist_id'
                )
                ->select('item.*')
                ->selectRaw("
                    COALESCE(
                        NULLIF(artist.stage_name, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
                ->where('item.id', $id)
                ->first();

            if (!$item) {
                throw ValidationException::withMessages([
                    'id' => $kind === 'song'
                        ? 'No listing was found with this ID.'
                        : 'No album was found with this ID.',
                ]);
            }

            if (
                $kind === 'song'
                && (int) $item->listing_type !== 1
            ) {
                throw ValidationException::withMessages([
                    'id' => 'Select an audio listing, not a video.',
                ]);
            }

            if ($kind === 'song') {
                $types = DB::table('listing_features')
                    ->where('listing_id', $id)
                    ->whereIn('listing_feature_type', [1, 2])
                    ->pluck('listing_feature_type')
                    ->map(fn ($type) => (int) $type)
                    ->all();

                $songDay = in_array(1, $types, true);
                $songWeek = in_array(2, $types, true);
            }
        }

        return view(
            'admin.featured.form',
            compact(
                'kind',
                'prefix',
                'item',
                'songDay',
                'songWeek'
            )
        );
    }

    public function store(Request $request)
    {
        return $this->save($request);
    }

    public function update(Request $request, int $id)
    {
        return $this->save($request, $id);
    }

    private function save(Request $request, ?int $fixedId = null)
    {
        $kind = $this->kind($request);
        $prefix = $this->routePrefix($kind);

        $data = $request->validate([
            'id' => ['required', 'integer', 'min:1'],
            'song_day' => ['nullable', 'boolean'],
            'song_week' => ['nullable', 'boolean'],
        ]);

        $id = (int) $data['id'];

        if ($fixedId !== null && $id !== $fixedId) {
            throw ValidationException::withMessages([
                'id' => 'The selected ID does not match this entry.',
            ]);
        }

        $types = [];

        if ($kind === 'song') {
            if (!empty($data['song_day'])) {
                $types[] = 1;
            }

            if (!empty($data['song_week'])) {
                $types[] = 2;
            }

            if ($types === []) {
                throw ValidationException::withMessages([
                    'song_day' =>
                        'Choose Song of the Day, Song of the Week, or both.',
                ]);
            }
        }

        DB::transaction(function () use ($request, $kind, $id, $types) {
            $table = $kind === 'song' ? 'listings' : 'albums';

            $item = DB::table($table)
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$item) {
                throw ValidationException::withMessages([
                    'id' => 'The selected item no longer exists.',
                ]);
            }

            if ((int) $item->is_published !== 1) {
                throw ValidationException::withMessages([
                    'id' => 'Publish this item before featuring it.',
                ]);
            }

            if ($kind === 'song') {
                if ((int) $item->listing_type !== 1) {
                    throw ValidationException::withMessages([
                        'id' => 'Only audio listings can be featured here.',
                    ]);
                }

                // Remove only deselected day/week features.
                // Other recovered feature types remain untouched.
                DB::table('listing_features')
                    ->where('listing_id', $id)
                    ->whereIn('listing_feature_type', [1, 2])
                    ->whereNotIn('listing_feature_type', $types)
                    ->delete();

                foreach ($types as $type) {
                    // Reinsert the selected feature so it receives the newest ID.
                    DB::table('listing_features')
                        ->where('listing_id', $id)
                        ->where('listing_feature_type', $type)
                        ->delete();

                    $userId = (int) $request->user()->id;

                    DB::table('listing_features')->insert([
                        'listing_id' => $id,
                        'listing_feature_type' => $type,
                        'user_id' => $userId,
                        'posted_by' => in_array($userId, [4, 5], true)
                            ? $userId
                            : 5,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                            } else {
                $existing = DB::table('album_populars')
                    ->where('album_id', $id)
                    ->exists();

                if (!$existing) {
                    // Existing frontend sorts album rates descending.
                    $highestRate = DB::table('album_populars')
                        ->selectRaw(
                            'MAX(CAST(rate_no AS UNSIGNED)) as highest_rate'
                        )
                        ->value('highest_rate');

                    DB::table('album_populars')->insert([
                        'album_id' => $id,
                        'rate_no' => (string) ((int) $highestRate + 1),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        return redirect()
            ->route($prefix . '.index')
            ->with('success', $kind === 'song'
                ? 'Featured song saved.'
                : 'Featured album saved.');
    }

    public function destroy(Request $request, int $id)
    {
        $kind = $this->kind($request);
        $prefix = $this->routePrefix($kind);

        DB::transaction(function () use ($kind, $id) {
            $table = $kind === 'song' ? 'listings' : 'albums';

            DB::table($table)
                ->where('id', $id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($kind === 'song') {
                DB::table('listing_features')
                    ->where('listing_id', $id)
                    ->whereIn('listing_feature_type', [1, 2])
                    ->delete();
            } else {
                DB::table('album_populars')
                    ->where('album_id', $id)
                    ->delete();
            }
        });

        return redirect()
            ->route($prefix . '.index')
            ->with('success', 'Item removed from featured content.');
    }
}