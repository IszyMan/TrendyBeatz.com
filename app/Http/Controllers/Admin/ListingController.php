<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ListingController extends Controller
{
    private const LISTING_TYPES = [
        'Audio' => 1,
        'video' => 2,
    ];

    private const COUNTRIES = [
        'naija' => 1,
        'ghana' => 2,
        'african' => 3,
    ];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $type = (string) $request->query('type', '');

        $listings = DB::table('listings as listing')
            ->leftJoin(
                'artists as artist',
                'artist.id',
                '=',
                'listing.artist_id'
            )
            ->leftJoin(
                'users as poster',
                'poster.id',
                '=',
                'listing.posted_by'
            )
            ->select(
                'listing.id',
                'listing.track_title',
                'listing.listing_type',
                'listing.is_published',
                'listing.created_at',
                'listing.track_title as TrackTitle',
                'poster.name as posted_by_name'
            )
            ->selectRaw("
                CASE listing.listing_type
                    WHEN '1' THEN 'Audio'
                    WHEN '2' THEN 'video'
                    ELSE ''
                END as ListingType
            ")
            ->selectRaw("
                CASE listing.country_id
                    WHEN '1' THEN 'naija'
                    WHEN '2' THEN 'ghana'
                    WHEN '3' THEN 'african'
                    ELSE ''
                END as country_id
            ")
            ->selectRaw("
                CASE listing.is_published
                    WHEN '1' THEN 'YES'
                    ELSE 'NO'
                END as IsPublished
            ")
            ->selectRaw("
                COALESCE(
                    NULLIF(TRIM(artist.stage_name), ''),
                    NULLIF(TRIM(artist.full_name), ''),
                    'Unknown artist'
                ) as artist_name
            ")
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where(
                            'listing.track_title',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'artist.stage_name',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'artist.full_name',
                            'like',
                            '%' . $search . '%'
                        );

                    if (ctype_digit($search)) {
                        $query->orWhere('listing.id', (int) $search);
                    }
                });
            })
            ->when(
                isset(self::LISTING_TYPES[$type]),
                fn ($query) => $query->where(
                    'listing.listing_type',
                    self::LISTING_TYPES[$type]
                )
            )
            ->orderByDesc('listing.id')
            ->paginate(20)
            ->withQueryString();

        return view(
            'admin.listings.index',
            compact('listings', 'search', 'type')
        );
    }

    public function create(): View
    {
        return view('admin.listings.form', [
            'listing' => null,
            'artists' => $this->artists(),
        ]);
    }

    public function edit(int $listing): View
    {
        $row = DB::table('listings')
            ->where('id', $listing)
            ->firstOrFail();

        return view('admin.listings.form', [
            'listing' => $this->formListing($row),
            'artists' => $this->artists(),
        ]);
    }

    public function artistAlbums(string $artist): JsonResponse
    {
        abort_unless(
            ctype_digit($artist)
                && DB::table('artists')
                    ->where('id', (int) $artist)
                    ->exists(),
            404
        );

        $albums = DB::table('albums')
            ->select('id', 'title')
            ->where('artist_id', (int) $artist)
            ->where('is_published', 1)
            ->orderBy('title')
            ->get();

        return response()->json($albums);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        // Check the media value before moving an uploaded cover.
        $mediaFilename = filled($data['media_url'] ?? null)
            ? $this->mediaFilename($data['media_url'])
            : null;

        $insert = $this->listingValues($data);

        $userId = (int) $request->user()->id;

        $insert['user_id'] = $userId;
        $insert['posted_by'] = in_array($userId, [4, 5], true)
            ? $userId
            : 5;

        $insert['track_url'] = $mediaFilename;
        $insert['cover_url'] = null;
        $insert['created_at'] = now();
        $insert['updated_at'] = now();

        if (filled($data['listing_id'] ?? null)) {
            $insert['id'] = (int) $data['listing_id'];
        }

        $newCover = null;

        try {
            if ($request->hasFile('cover_image')) {
                $newCover = $this->saveCover(
                    $request->file('cover_image')
                );

                $insert['cover_url'] = $newCover;
            }

            $id = DB::table('listings')->insertGetId($insert);
        } catch (\Throwable $exception) {
            if ($newCover !== null) {
                File::delete(public_path('images/' . $newCover));
            }

            throw $exception;
        }

        return redirect()
            ->route('admin.listings.edit', $id)
            ->with('success', 'Listing created.');
    }

    public function update(
        Request $request,
        int $listing
    ): RedirectResponse {
        $existing = DB::table('listings')
            ->where('id', $listing)
            ->firstOrFail();

        $data = $this->validated($request, $listing);

        $mediaFilename = filled($data['media_url'] ?? null)
            ? $this->mediaFilename($data['media_url'])
            : null;

        $values = $this->listingValues($data);
        $values['updated_at'] = now();

        // A blank media field keeps the existing filename.
        if ($mediaFilename !== null) {
            $values['track_url'] = $mediaFilename;
        }

        $newCover = null;

        try {
            if ($request->hasFile('cover_image')) {
                $newCover = $this->saveCover(
                    $request->file('cover_image')
                );

                $values['cover_url'] = $newCover;
            }

            // Keep the original user_id, posted_by and created_at.
            DB::table('listings')
                ->where('id', $existing->id)
                ->update($values);
        } catch (\Throwable $exception) {
            if ($newCover !== null) {
                File::delete(public_path('images/' . $newCover));
            }

            throw $exception;
        }

        return redirect()
            ->route('admin.listings.edit', $existing->id)
            ->with('success', 'Listing updated.');
    }

    public function destroy(int $listing): RedirectResponse
    {
        DB::transaction(function () use ($listing) {
            $row = DB::table('listings')
                ->where('id', $listing)
                ->lockForUpdate()
                ->firstOrFail();

            // Remove the deleted listing's feature references.
            DB::table('listing_features')
                ->where('listing_id', $row->id)
                ->delete();

            DB::table('listings')
                ->where('id', $row->id)
                ->delete();
        });

        return redirect()
            ->route('admin.listings.index')
            ->with('success', 'Listing deleted.');
    }

    private function artists()
    {
        return DB::table('artists')
            ->select(
                'id',
                'stage_name',
                'full_name',
                'id as Artists_Id',
                'stage_name as Stage_Name',
                'full_name as ArtistsName'
            )
            ->where('is_published', 1)
            ->orderBy('stage_name')
            ->get()
            ->map(function ($artist) {
                // Existing form comparisons may use strict equality.
                $artist->Artists_Id = (string) $artist->id;

                return $artist;
            });
    }

    /**
     * Supply the field names expected by the current edit form.
     */
    private function formListing(object $row): object
    {
        $row->Artists_Id = (string) $row->artist_id;
        $row->TrackTitle = $row->track_title;
        $row->Featuring = $row->featuring;
        $row->YearOfRelease = $row->released_year;
        $row->TrackUrl = $row->track_url;
        $row->CoverUrl = $row->cover_url;
        $row->TrackInfo = $row->track_info;
        $row->trackinfo1 = $row->track_info1;
        $row->trackinfo2 = $row->track_info2;
        $row->producedby = $row->produced_by;
        $row->directedby = $row->directed_by;
        $row->scriptUrl = $row->script_url;
        $row->isgospel = (int) $row->is_gospel;
        $row->ishighlife = (int) $row->is_high_life;

        $row->ListingType = match ((int) $row->listing_type) {
            1 => 'Audio',
            2 => 'video',
            default => '',
        };

        $row->country_id = match ((int) $row->country_id) {
            1 => 'naija',
            2 => 'ghana',
            3 => 'african',
            default => '',
        };

        $row->IsPublished = (int) $row->is_published === 1
            ? 'YES'
            : 'NO';

        return $row;
    }

    private function validated(
        Request $request,
        ?int $listing = null
    ): array {
        // These input names match your existing listing form.
        $rules = [
            'Artists_Id' => [
                'required',
                'integer',
                Rule::exists('artists', 'id'),
            ],
            'album_id' => ['nullable', 'integer', 'min:0'],
            'TrackTitle' => ['required', 'string', 'max:191'],
            'ListingType' => [
                'required',
                Rule::in(array_keys(self::LISTING_TYPES)),
            ],
            'country_id' => [
                'required',
                Rule::in(array_keys(self::COUNTRIES)),
            ],
            'Featuring' => ['nullable', 'string', 'max:191'],
            'YearOfRelease' => ['nullable', 'string', 'max:191'],
            'track_number' => ['nullable', 'integer', 'min:1'],
            'cover_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:5120',
            ],
            'media_url' => ['nullable', 'string', 'max:2000'],
            'buy_song' => ['nullable', 'string', 'max:191'],
            'introduction' => ['nullable', 'string'],
            'track_info_1' => ['nullable', 'string', 'max:5000'],
            'track_info_2' => ['nullable', 'string', 'max:5000'],
            'track_info_3' => ['nullable', 'string', 'max:5000'],
            'producedby' => ['nullable', 'string', 'max:191'],
            'directedby' => ['nullable', 'string', 'max:191'],
            'scriptUrl' => ['nullable', 'string'],
            'youtube_embed_url' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'audiomack_embed_url' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'isgospel' => ['nullable', 'boolean'],
            'ishighlife' => ['nullable', 'boolean'],
            'IsPublished' => ['nullable', 'boolean'],
        ];

        if ($listing === null) {
            $rules['listing_id'] = [
                'nullable',
                'integer',
                'min:1',
                'max:2147483647',
                Rule::unique('listings', 'id'),
            ];
        }

        $data = $request->validate($rules);

        foreach ([
            'youtube_embed_url',
            'audiomack_embed_url',
        ] as $field) {
            $data[$field] = filled($data[$field] ?? null)
                ? $this->embedSource($data[$field], $field)
                : null;
        }

        if (!empty($data['album_id'])) {
            $albumExists = DB::table('albums')
                ->where('id', (int) $data['album_id'])
                ->where('artist_id', (int) $data['Artists_Id'])
                ->exists();

            if (!$albumExists) {
                throw ValidationException::withMessages([
                    'album_id' =>
                        'Select an album belonging to this artist.',
                ]);
            }
        } else {
            $data['album_id'] = null;
        }

        return $data;
    }

    private function listingValues(array $data): array
    {
        $artist = DB::table('artists')
            ->select('stage_name', 'full_name')
            ->where('id', (int) $data['Artists_Id'])
            ->firstOrFail();

        $artistName = trim((string) $artist->stage_name);

        if ($artistName === '') {
            $artistName = trim((string) $artist->full_name);
        }

        if ($artistName === '') {
            $artistName = 'TrendyBeatz';
        }

        $slugParts = trim(
            $artistName . ' ' . $data['TrackTitle']
        );

        if (
            filled($data['Featuring'] ?? null)
            && !preg_match(
                '/\b(?:ft|feat|featuring)\.?\s/i',
                $data['TrackTitle']
            )
        ) {
            $slugParts .= ' ft ' . $data['Featuring'];
        }

        return [
            'artist_id' => (int) $data['Artists_Id'],
            'album_id' => $data['album_id'] === null
                ? null
                : (int) $data['album_id'],
            'track_title' => $data['TrackTitle'],
            'listing_type' => self::LISTING_TYPES[
                $data['ListingType']
            ],
            'country_id' => self::COUNTRIES[$data['country_id']],
            'featuring' => $data['Featuring'] ?? null,
            'released_year' => $data['YearOfRelease'] ?? null,
            'track_number' => $data['track_number'] ?? 1,
            'buy_song' => $data['buy_song'] ?? null,
            'introduction' => $data['introduction'] ?? null,
            'track_info' => $data['track_info_1'] ?? null,
            'track_info1' => $data['track_info_2'] ?? null,
            'track_info2' => $data['track_info_3'] ?? null,
            'produced_by' => $data['producedby'] ?? null,
            'directed_by' => $data['directedby'] ?? null,
            'script_url' => $data['scriptUrl'] ?? null,
            'youtube_embed_url' =>
                $data['youtube_embed_url'] ?? null,
            'audiomack_embed_url' =>
                $data['audiomack_embed_url'] ?? null,
            'is_gospel' => (int) ($data['isgospel'] ?? 0),
            'is_high_life' => (int) ($data['ishighlife'] ?? 0),
            'is_published' => (int) ($data['IsPublished'] ?? 0),
            'slug' => Str::slug($slugParts),
        ];
    }

    private function saveCover(UploadedFile $file): string
    {
        $directory = public_path('images');

        File::ensureDirectoryExists($directory);

        $filename = 'listing-' . Str::uuid()
            . '.' . $file->extension();

        $file->move($directory, $filename);

        return $filename;
    }

    private function mediaFilename(string $value): string
    {
        $value = trim($value);

        if (preg_match('~^https?://~i', $value)) {
            $path = parse_url($value, PHP_URL_PATH);
            $filename = rawurldecode(
                basename((string) $path)
            );
        } else {
            $filename = $value;
        }

        if (
            $filename === ''
            || $filename === '.'
            || $filename === '..'
            || str_contains($filename, '/')
            || str_contains($filename, '\\')
            || preg_match('/[\x00-\x1F\x7F]/', $filename)
            || mb_strlen($filename, 'UTF-8') > 191
        ) {
            throw ValidationException::withMessages([
                'media_url' =>
                    'Enter a media filename or a URL ending in a filename. '
                    . 'The filename must not exceed 191 characters.',
            ]);
        }

        return $filename;
    }

    /**
     * Accept an iframe or embed URL and store its validated source URL.
     */
    private function embedSource(
        string $input,
        string $field
    ): string {
        $input = trim($input);

        if (str_contains($input, '<')) {
            if (!preg_match(
                '~<iframe\b[^>]*\bsrc\s*=\s*(["\'])(.*?)\1~is',
                $input,
                $matches
            )) {
                throw ValidationException::withMessages([
                    $field => 'Paste an iframe with a valid src URL.',
                ]);
            }

            $input = html_entity_decode(
                $matches[2],
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            );
        }

        $parts = parse_url($input);

        if ($parts === false) {
            throw ValidationException::withMessages([
                $field => 'The embed URL is invalid.',
            ]);
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $path = (string) ($parts['path'] ?? '');

        if (
            $scheme !== 'https'
            || isset($parts['user'])
            || isset($parts['pass'])
            || (
                isset($parts['port'])
                && $parts['port'] !== 443
            )
        ) {
            throw ValidationException::withMessages([
                $field => 'The embed must use a valid HTTPS URL.',
            ]);
        }

        if ($field === 'youtube_embed_url') {
            if (
                !in_array(
                    $host,
                    [
                        'youtube.com',
                        'www.youtube.com',
                        'youtube-nocookie.com',
                        'www.youtube-nocookie.com',
                    ],
                    true
                )
                || !preg_match(
                    '~^/embed/([A-Za-z0-9_-]{11})/?$~',
                    $path,
                    $matches
                )
            ) {
                throw ValidationException::withMessages([
                    $field => 'Paste a YouTube embed iframe.',
                ]);
            }

            return 'https://www.youtube-nocookie.com/embed/'
                . $matches[1];
        }

        if (
            !in_array(
                $host,
                ['audiomack.com', 'www.audiomack.com'],
                true
            )
            || !preg_match(
                '~^/embed/[A-Za-z0-9_-]+/'
                . '(song|album|playlist)/[A-Za-z0-9_-]+/?$~',
                $path
            )
        ) {
            throw ValidationException::withMessages([
                $field => 'Paste an Audiomack embed iframe.',
            ]);
        }

        return 'https://audiomack.com' . rtrim($path, '/');
    }
}