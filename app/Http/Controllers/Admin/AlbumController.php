<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Support\ActivityLogger;

class AlbumController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $albums = DB::table('albums as album')
            ->leftJoin(
                'artists as artist',
                'artist.id',
                '=',
                'album.artist_id'
            )
            ->select(
                'album.*',
                'artist.stage_name',
                'artist.full_name',
                'artist.stage_name as Stage_Name',
                'artist.full_name as ArtistsName'
            )
            ->selectRaw("
                COALESCE(
                    NULLIF(TRIM(artist.stage_name), ''),
                    'TrendyBeatz'
                ) as artist_name
            ")
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('album.title', 'like', "%{$search}%")
                        ->orWhere(
                            'artist.stage_name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'artist.full_name',
                            'like',
                            "%{$search}%"
                        );

                    if (ctype_digit($search)) {
                        $query->orWhere('album.id', (int) $search);
                    }
                });
            })
            ->orderByDesc('album.id')
            ->paginate(20)
            ->withQueryString();

        $albums->getCollection()->transform(
            fn ($album) => $this->viewAlbum($album)
        );

        return view(
            'admin.albums.index',
            compact('albums', 'search')
        );
    }

    public function create()
    {
        return view('admin.albums.create', [
            'album' => null,
            'artists' => $this->artists(),
        ]);
    }

    public function store(Request $request)
    {
        $idData = $request->validate([
            'album_id' => [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('albums', 'id'),
            ],
        ], [
            'album_id.unique' => 'This Album ID already exists. Choose another ID.',
        ]);

        $data = $this->validated($request);
        $values = $this->albumValues($data);

        if (filled($idData['album_id'] ?? null)) {
            $values['id'] = (int) $idData['album_id'];
        }

        $filename = null;

        try {
            if ($request->hasFile('cover_image')) {
                $filename = $this->saveImage(
                    $request->file('cover_image')
                );

                $values['cover_url'] = $filename;
            }

            $userId = (int) $request->user()->id;

            $values['user_id'] = $userId;
            $values['posted_by'] = in_array($userId, [4, 5], true)
                ? $userId
                : 5;

            $values['created_at'] = now();
            $values['updated_at'] = now();

            $albumId = DB::transaction(function () use ($values) {
                $albumId = DB::table('albums')->insertGetId($values);
            
                if ((int) $values['is_published'] === 1) {
                    ActivityLogger::record(
                        'published',
                        'album',
                        $albumId,
                        $values['title']
                    );
                }
            
                return $albumId;
            });
        } catch (\Throwable $exception) {
            if ($filename !== null) {
                File::delete(public_path('images/' . $filename));
            }

            if (
                $exception instanceof \Illuminate\Database\QueryException
                && (int) ($exception->errorInfo[1] ?? 0) === 1062
                && isset($values['id'])
                && DB::table('albums')->where('id', $values['id'])->exists()
            ) {
                throw ValidationException::withMessages([
                    'album_id' => 'This Album ID already exists. Choose another ID.',
                ]);
            }

            throw $exception;
        }

        return redirect()
            ->route('admin.albums.edit', ['album' => $albumId])
            ->with('success', 'Album created Successfully.');
    }

    public function edit(int $album)
    {
        $album = DB::table('albums')
            ->where('id', $album)
            ->firstOrFail();

        return view('admin.albums.edit', [
            'album' => $this->viewAlbum($album),
            'artists' => $this->artists(),
        ]);
    }

    public function update(Request $request, int $album)
    {
        $row = DB::table('albums')
            ->where('id', $album)
            ->firstOrFail();

        $data = $this->validated($request);

        // Prevent changing the album's artist while its tracks
        // still belong to another artist.
        if (
            (string) $row->artist_id !== (string) $data['artist_id']
            && DB::table('listings')
                ->where('album_id', $row->id)
                ->where(function ($query) use ($data) {
                    $query
                        ->whereNull('artist_id')
                        ->orWhere(
                            'artist_id',
                            '<>',
                            (int) $data['artist_id']
                        );
                })
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'artist_id' =>
                    'Reassign this album’s listings to the selected artist '
                    . 'before changing the album artist.',
            ]);
        }

        $values = $this->albumValues($data);
        $values['updated_at'] = now();

        $filename = null;

        try {
            if ($request->hasFile('cover_image')) {
                $filename = $this->saveImage(
                    $request->file('cover_image')
                );

                $values['cover_url'] = $filename;
            }

            // Preserve user_id, posted_by and created_at.
            // With no new upload, preserve the existing cover.
            DB::transaction(function () use ($row, $values) {
                $before = DB::table('albums')
                    ->where('id', $row->id)
                    ->lockForUpdate()
                    ->firstOrFail();
            
                $changed = false;
            
                foreach ($values as $field => $value) {
                    if ($field === 'updated_at') {
                        continue;
                    }
            
                    if ((string) ($before->{$field} ?? '') !== (string) ($value ?? '')) {
                        $changed = true;
                        break;
                    }
                }
            
                DB::table('albums')
                    ->where('id', $before->id)
                    ->update($values);
            
                if ($changed) {
                    $newlyPublished =
                        (int) $before->is_published !== 1
                        && (int) $values['is_published'] === 1;
            
                    ActivityLogger::record(
                        $newlyPublished ? 'published' : 'updated',
                        'album',
                        $before->id,
                        $values['title']
                    );
                }
            });
        } catch (\Throwable $exception) {
            if ($filename !== null) {
                File::delete(public_path('images/' . $filename));
            }

            throw $exception;
        }

        if ($filename !== null) {
            $this->deleteUploadedImage($row->cover_url);
        }

        return redirect()
            ->route('admin.albums.edit', ['album' => $album])
            ->with('success', 'Album updated Successfully.');
    }

    public function destroy(int $album)
    {
        $row = DB::table('albums')
            ->where('id', $album)
            ->firstOrFail();

        if (
            DB::table('listings')
                ->where('album_id', $row->id)
                ->exists()
        ) {
            return back()->withErrors([
                'album' =>
                    'This album has listings. Remove or reassign them first.',
            ]);
        }

        DB::transaction(function () use ($row) {
            DB::table('album_populars')
                ->where('album_id', $row->id)
                ->delete();
        
            DB::table('albums')
                ->where('id', $row->id)
                ->delete();
        
            ActivityLogger::record(
                'deleted',
                'album',
                $row->id,
                $row->title
            );
        });

        $this->deleteUploadedImage($row->cover_url);

        return redirect()
            ->route('admin.albums.index')
            ->with('success', 'Album deleted.');
    }

    private function artists()
    {
        return DB::table('artists')
            ->select(
                'id',
                'artist_id',
                'stage_name',
                'full_name',
                'stage_name as Stage_Name',
                'full_name as ArtistsName'
            )
            ->orderBy('stage_name')
            ->get()
            ->map(function ($artist) {
                // Compatibility with the existing album dropdown.
                // This value is the numeric relationship ID,
                // not the artist's TB code.
                $artist->Artists_Id = (string) $artist->id;

                return $artist;
            });
    }

    private function viewAlbum(object $album): object
    {
        $album->artist_id = (string) $album->artist_id;

        $album->IsPublished = (int) $album->is_published === 1
            ? 'YES'
            : 'NO';

        return $album;
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'artist_id' => [
                'required',
                'integer',
                Rule::exists('artists', 'id'),
            ],
            'title' => ['required', 'string', 'max:191'],
            'featuring' => ['nullable', 'string', 'max:191'],
            'description' => ['nullable', 'string'],
            'released_year' => [
                'required',
                'regex:/^(19|20)\d{2}$/',
            ],
            'released_date' => ['nullable', 'date_format:Y-m-d'],
            'IsPublished' => [
                'required',
                Rule::in(['YES', 'NO']),
            ],
            'buy_song' => ['nullable', 'url:http,https', 'max:2048'],
            'cover_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:5120',
            ],
        ]);

        $data['title'] = trim($data['title']);

        if ($data['title'] === '') {
            throw ValidationException::withMessages([
                'title' => 'Enter an album title.',
            ]);
        }

        return $data;
    }

    private function albumValues(array $data): array
    {
        $artist = DB::table('artists')
            ->select('stage_name')
            ->where('id', (int) $data['artist_id'])
            ->firstOrFail();

        $artistName = trim((string) $artist->stage_name);

        if ($artistName === '') {
            $artistName = 'TrendyBeatz';
        }

        return [
            'artist_id' => (int) $data['artist_id'],
            'title' => $data['title'],
            'slug' => Str::slug(
                $artistName . ' ' . $data['title']
            ),
            'featuring' => $data['featuring'] ?? null,
            'description' => $data['description'] ?? null,
            'buy_song' => filled($data['buy_song'] ?? null)
                ? trim($data['buy_song'])
                : null,
            'released_year' => $data['released_year'],
            'released_date' => $data['released_date'] ?? null,
            'is_published' => $data['IsPublished'] === 'YES'
                ? 1
                : 0,
        ];
    }

    private function saveImage(UploadedFile $image): string
    {
        $directory = public_path('images');

        File::ensureDirectoryExists($directory);

        $originalName = pathinfo(
            $image->getClientOriginalName(),
            PATHINFO_FILENAME
        );

        $baseName = Str::limit(
            Str::slug($originalName) ?: 'image',
            140,
            ''
        );

        $extension = $image->extension();
        $filename = $baseName . '.' . $extension;
        $counter = 1;

        // Avoid overwriting an existing image.
        while (File::exists($directory . DIRECTORY_SEPARATOR . $filename)) {
            $filename = $baseName . '-' . $counter . '.' . $extension;
            $counter++;
        }

        $image->move($directory, $filename);

        return $filename;
    }

    private function deleteUploadedImage(?string $value): void
    {
        $filename = trim((string) $value);

        // Accept only an image filename without directory paths.
        if (!preg_match(
            '/\A[a-z0-9_-]+\.(jpg|jpeg|png|webp|gif)\z/i',
            $filename
        )) {
            return;
        }

        // Keep images still used by an album, artist or listing.
        if (
            DB::table('albums')->where('cover_url', $filename)->exists()
            || DB::table('artists')->where('img', $filename)->exists()
            || DB::table('listings')->where('cover_url', $filename)->exists()
        ) {
            return;
        }

        File::delete(public_path('images/' . $filename));
    }
}