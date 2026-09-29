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
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $type = (string) $request->query('type', '');

        $listings = DB::table('listing as listing')
            ->leftJoin(
                'artists as artist',
                'artist.Artists_Id',
                '=',
                'listing.Artists_Id'
            )
            ->leftJoin(
                'users as poster',
                'poster.id',
                '=',
                'listing.posted_by'
            )
            ->select(
                'listing.id',
                'listing.TrackTitle',
                'listing.ListingType',
                'listing.country_id',
                'listing.IsPublished',
                'listing.created_at',
                'poster.name as posted_by_name',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'Unknown artist'
                    ) as artist_name
                ")
            )
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where(
                            'listing.TrackTitle',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'artist.Stage_Name',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'artist.ArtistsName',
                            'like',
                            '%' . $search . '%'
                        );

                    if (ctype_digit($search)) {
                        $query->orWhere(
                            'listing.id',
                            (int) $search
                        );
                    }
                });
            })
            ->when(
                in_array($type, ['Audio', 'video'], true),
                fn ($query) => $query->where(
                    'listing.ListingType',
                    $type
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
        $row = DB::table('listing')
            ->where('id', $listing)
            ->firstOrFail();

        return view('admin.listings.form', [
            'listing' => $row,
            'artists' => $this->artists(),
        ]);
    }

    public function artistAlbums(string $artist): JsonResponse
    {
        abort_unless(
            DB::table('artists')
                ->where('Artists_Id', $artist)
                ->exists(),
            404
        );

        $albums = DB::table('albums')
            ->select('id', 'title')
            ->where('artist_id', $artist)
            ->where('IsPublished', 'YES')
            ->orderBy('title')
            ->get();

        return response()->json($albums);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        // Validate the media value before moving an uploaded image.
        $mediaFilename = filled($data['media_url'] ?? null)
            ? $this->mediaFilename($data['media_url'])
            : null;

        $insert = $this->listingValues($data);

        $userId = (int) $request->user()->id;

        $insert['user_id'] = $userId;
        $insert['posted_by'] = in_array($userId, [4, 5], true)
            ? $userId
            : 5;

        $insert['TrackUrl'] = $mediaFilename;
        $insert['CoverUrl'] = null;

        if (filled($data['listing_id'] ?? null)) {
            $insert['id'] = (int) $data['listing_id'];
        }

        $newCover = null;

        try {
            if ($request->hasFile('cover_image')) {
                $newCover = $this->saveCover(
                    $request->file('cover_image')
                );

                $insert['CoverUrl'] = $newCover;
            }

            $id = DB::table('listing')->insertGetId($insert);
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
        $existing = DB::table('listing')
            ->where('id', $listing)
            ->firstOrFail();

        $data = $this->validated($request, $listing);

        $mediaFilename = filled($data['media_url'] ?? null)
            ? $this->mediaFilename($data['media_url'])
            : null;

        $values = $this->listingValues($data);

        // Blank fields keep the existing image and media filename.
        if ($mediaFilename !== null) {
            $values['TrackUrl'] = $mediaFilename;
        }

        $newCover = null;

        try {
            if ($request->hasFile('cover_image')) {
                $newCover = $this->saveCover(
                    $request->file('cover_image')
                );

                $values['CoverUrl'] = $newCover;
            }

            // Preserve the original creator and public poster.
            DB::table('listing')
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
        DB::table('listing')
            ->where('id', $listing)
            ->firstOrFail();

        DB::table('listing')
            ->where('id', $listing)
            ->delete();

        return redirect()
            ->route('admin.listings.index')
            ->with('success', 'Listing deleted.');
    }

    private function artists()
    {
        return DB::table('artists')
            ->select('Artists_Id', 'Stage_Name', 'ArtistsName')
            ->where('IsPublished', 'YES')
            ->orderBy('Stage_Name')
            ->get();
    }

    private function validated(
        Request $request,
        ?int $listing = null
    ): array {
        $rules = [
            'Artists_Id' => [
                'required',
                'string',
                Rule::exists('artists', 'Artists_Id'),
            ],
            'album_id' => ['nullable', 'integer'],
            'TrackTitle' => ['required', 'string', 'max:1000'],
            'ListingType' => [
                'required',
                Rule::in(['Audio', 'video']),
            ],
            'country_id' => [
                'required',
                Rule::in(['naija', 'ghana', 'african']),
            ],
            'Featuring' => ['nullable', 'string', 'max:500'],
            'YearOfRelease' => ['nullable', 'string', 'max:100'],
            'track_number' => ['nullable', 'integer', 'min:1'],
            'cover_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:5120',
            ],
            'media_url' => ['nullable', 'string', 'max:2000'],
            'buy_song' => ['nullable', 'string', 'max:500'],
            'introduction' => ['nullable', 'string'],
            'track_info_1' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'track_info_2' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'track_info_3' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'producedby' => ['nullable', 'string', 'max:500'],
            'directedby' => ['nullable', 'string', 'max:36'],
            'scriptUrl' => ['nullable', 'string'],
            'youtube_embed_url' => ['nullable', 'string', 'max:5000'],
            'audiomack_embed_url' => ['nullable', 'string', 'max:5000'],
            'isgospel' => ['nullable', 'boolean'],
            'ishighlife' => ['nullable', 'boolean'],
            'IsPublished' => ['nullable', 'boolean'],
        ];

        if ($listing === null) {
            $rules['listing_id'] = [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('listing', 'id'),
            ];
        }

        $data = $request->validate($rules);

        foreach (['youtube_embed_url', 'audiomack_embed_url'] as $field) {
            $data[$field] = filled($data[$field] ?? null)
                ? $this->embedSource($data[$field], $field)
                : null;
        }

        if (filled($data['album_id'] ?? null)) {
            $album = DB::table('albums')
                ->select('id', 'title')
                ->where('id', $data['album_id'])
                ->where('artist_id', $data['Artists_Id'])
                ->first();

            if (!$album) {
                throw ValidationException::withMessages([
                    'album_id' =>
                        'Select an album belonging to this artist.',
                ]);
            }

            $data['album_name'] = $album->title;
        } else {
            $data['album_id'] = 0;
            $data['album_name'] = null;
        }

        return $data;
    }

    private function listingValues(array $data): array
    {
        $artist = DB::table('artists')
            ->select('Stage_Name', 'ArtistsName')
            ->where('Artists_Id', $data['Artists_Id'])
            ->first();

        $artistName = trim((string) (
            $artist->Stage_Name ?: $artist->ArtistsName
        ));

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
            'Artists_Id' => $data['Artists_Id'],
            'album_id' => (int) $data['album_id'],
            'AlbumName' => $data['album_name'],
            'TrackTitle' => $data['TrackTitle'],
            'ListingType' => $data['ListingType'],
            'country_id' => $data['country_id'],
            'Featuring' => $data['Featuring'] ?? null,
            'YearOfRelease' => $data['YearOfRelease'] ?? null,
            'track_number' => $data['track_number'] ?? 1,
            'buy_song' => $data['buy_song'] ?? null,
            'introduction' => $data['introduction'] ?? null,
            'TrackInfo' => $data['track_info_1'] ?? null,
            'trackinfo1' => $data['track_info_2'] ?? null,
            'trackinfo2' => $data['track_info_3'] ?? null,
            'producedby' => $data['producedby'] ?? null,
            'directedby' => $data['directedby'] ?? null,
            'scriptUrl' => $data['scriptUrl'] ?? null,
            'youtube_embed_url' =>
                $data['youtube_embed_url'] ?? null,
            'audiomack_embed_url' =>
                $data['audiomack_embed_url'] ?? null,
            'isgospel' => (int) ($data['isgospel'] ?? 0),
            'ishighlife' => (int) ($data['ishighlife'] ?? 0),
            'IsPublished' => !empty($data['IsPublished'])
                ? 'YES'
                : 'NO',
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
            || strlen($filename) > 1000
        ) {
            throw ValidationException::withMessages([
                'media_url' =>
                    'Enter a media filename or a URL ending in a filename.',
            ]);
        }

        return $filename;
    }

    private function embedSource(string $input, string $field): string
    {
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
        $host = strtolower((string) ($parts['host'] ?? ''));
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $path = (string) ($parts['path'] ?? '');

        if ($scheme !== 'https' || isset($parts['user']) || isset($parts['pass'])) {
            throw ValidationException::withMessages([
                $field => 'The embed must use a valid HTTPS URL.',
            ]);
        }

        if ($field === 'youtube_embed_url') {
            if (
                !in_array(
                    $host,
                    ['www.youtube.com', 'youtube.com', 'www.youtube-nocookie.com'],
                    true
                )
                || !preg_match(
                    '~^/embed/([A-Za-z0-9_-]{11})$~',
                    $path,
                    $matches
                )
            ) {
                throw ValidationException::withMessages([
                    $field => 'Paste a YouTube embed iframe.',
                ]);
            }

            return 'https://www.youtube-nocookie.com/embed/' . $matches[1];
        }

        if (
            !in_array($host, ['audiomack.com', 'www.audiomack.com'], true)
            || !preg_match(
                '~^/embed/[A-Za-z0-9_-]+/(song|album|playlist)/[A-Za-z0-9_-]+/?$~',
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