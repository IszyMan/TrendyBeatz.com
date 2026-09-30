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

class ArtistController extends Controller
{
    private const COUNTRIES = [
        'naija' => 1,
        'ghana' => 2,
        'african' => 3,
    ];

    public function index()
    {
        $artists = DB::table('artists')
            ->orderByDesc('id')
            ->paginate(20);

        $artists->getCollection()->transform(
            fn ($artist) => $this->viewArtist($artist)
        );

        return view('admin.artists.index', compact('artists'));
    }

    public function create()
    {
        return view('admin.artists.create', [
            'artist' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->acquireWriteLock();

        $filename = null;

        try {
            $this->rejectDuplicateName($data['Stage_Name']);

            $values = $this->artistValues($data);

            if ($request->hasFile('profile_image')) {
                $filename = $this->saveImage(
                    $request->file('profile_image')
                );

                $values['img'] = $filename;
            }

            $userId = (int) $request->user()->id;

            $values['user_id'] = $userId;

            // Public authors remain users 4 and 5.
            $values['posted_by'] = in_array($userId, [4, 5], true)
                ? $userId
                : 5;

            $values['created_at'] = now();
            $values['updated_at'] = now();

            // MySQL assigns the numeric primary key automatically.
            DB::transaction(function () use ($values) {
                $id = DB::table('artists')->insertGetId($values);

                DB::table('artists')
                    ->where('id', $id)
                    ->update([
                        'artist_id' => 'TB' . str_pad(
                            (string) $id,
                            3,
                            '0',
                            STR_PAD_LEFT
                        ),
                    ]);
            });
        } catch (\Throwable $exception) {
            if ($filename !== null) {
                File::delete(public_path('images/' . $filename));
            }

            throw $exception;
        } finally {
            $this->releaseWriteLock();
        }

        return redirect()
            ->route('admin.artists.index')
            ->with('success', 'Artist created.');
    }

    public function edit(int $artist)
    {
        $artist = DB::table('artists')
            ->where('id', $artist)
            ->firstOrFail();

        $artist = $this->viewArtist($artist);

        return view('admin.artists.edit', compact('artist'));
    }

    public function update(Request $request, int $artist)
    {
        $row = DB::table('artists')
            ->where('id', $artist)
            ->firstOrFail();

        $data = $this->validated($request);
        $this->acquireWriteLock();

        $filename = null;

        try {
            $this->rejectDuplicateName(
                $data['Stage_Name'],
                (int) $row->id
            );

            $values = $this->artistValues($data);

            if ($request->hasFile('profile_image')) {
                $filename = $this->saveImage(
                    $request->file('profile_image')
                );

                $values['img'] = $filename;
            }

            $values['updated_at'] = now();

            // Preserve id, user_id, posted_by and created_at.
            DB::table('artists')
                ->where('id', $row->id)
                ->update($values);
        } catch (\Throwable $exception) {
            if ($filename !== null) {
                File::delete(public_path('images/' . $filename));
            }

            throw $exception;
        } finally {
            $this->releaseWriteLock();
        }

        if ($filename !== null) {
            $this->deleteUploadedImage($row->img);
        }

        return redirect()
            ->route('admin.artists.index')
            ->with('success', 'Artist updated.');
    }

    public function destroy(int $artist)
    {
        $row = DB::table('artists')
            ->where('id', $artist)
            ->firstOrFail();

        if (
            DB::table('listings')
                ->where('artist_id', $row->id)
                ->exists()
            || DB::table('albums')
                ->where('artist_id', $row->id)
                ->exists()
        ) {
            return back()->withErrors([
                'artist' =>
                    'This artist has listings or albums. Remove those first.',
            ]);
        }

        DB::table('artists')
            ->where('id', $row->id)
            ->delete();

        $this->deleteUploadedImage($row->img);

        return redirect()
            ->route('admin.artists.index')
            ->with('success', 'Artist deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'ArtistsName' => ['nullable', 'string', 'max:191'],
            'Stage_Name' => ['required', 'string', 'max:191'],
            'Fullname' => ['nullable', 'string', 'max:191'],
            'ArtistsProfile' => ['nullable', 'string'],
            'RecordLabel' => ['nullable', 'string', 'max:191'],
            'Place_Birth' => ['nullable', 'string'],
            'Genres' => ['nullable', 'string', 'max:191'],
            'meta_keyword' => ['nullable', 'string'],
            'country_id' => [
                'required',
                Rule::in(array_keys(self::COUNTRIES)),
            ],
            'IsPublished' => [
                'required',
                Rule::in(['YES', 'NO']),
            ],
            'category_id' => [
                'required',
                'integer',
                'min:0',
                'max:2147483647',
            ],
            'order_id' => [
                'required',
                'integer',
                'min:0',
                'max:2147483647',
            ],
            'Is_also_comedian' => [
                'required',
                Rule::in(['0', '1']),
            ],
            'networth_id' => [
                'nullable',
                'integer',
                'min:0',
                'max:2147483647',
            ],
            'profile_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:5120',
            ],
        ]);

        $data['Stage_Name'] = trim($data['Stage_Name']);

        if (
            $data['Stage_Name'] === ''
            || Str::slug($data['Stage_Name']) === ''
        ) {
            throw ValidationException::withMessages([
                'Stage_Name' =>
                    'Enter a stage name that can be used in the artist URL.',
            ]);
        }

        return $data;
    }

    private function artistValues(array $data): array
    {
        $fullName = trim((string) ($data['Fullname'] ?? ''));

        if ($fullName === '') {
            $fullName = trim((string) ($data['ArtistsName'] ?? ''));
        }

        return [
            'stage_name' => $data['Stage_Name'],
            'full_name' => $fullName !== '' ? $fullName : null,
            'slug' => Str::slug($data['Stage_Name']),
            'artist_profile' => $data['ArtistsProfile'] ?? null,
            'record_label' => $data['RecordLabel'] ?? null,
            'place_of_birth' => $data['Place_Birth'] ?? null,
            'genre' => $data['Genres'] ?? null,
            'meta_keyword' => $data['meta_keyword'] ?? null,
            'country_id' => self::COUNTRIES[$data['country_id']],
            'is_published' => $data['IsPublished'] === 'YES' ? 1 : 0,
            'artist_type' => (int) $data['category_id'],
            'order_id' => (int) $data['order_id'],
            'is_also_comedian' => (int) $data['Is_also_comedian'],
            'networth_id' => $data['networth_id'] ?? null,
        ];
    }

    /**
     * Preserve the original database fields and supply aliases
     * expected by the existing admin views.
     */
    private function viewArtist(object $artist): object
    {
        $artist->Artists_Id = $artist->artist_id;
        $artist->Stage_Name = $artist->stage_name;
        $artist->ArtistsName = $artist->full_name;
        $artist->Fullname = $artist->full_name;
        $artist->ArtistsProfile = $artist->artist_profile;
        $artist->ProfilePic = $artist->img;
        $artist->RecordLabel = $artist->record_label;
        $artist->Place_Birth = $artist->place_of_birth;
        $artist->Genres = $artist->genre;
        $artist->category_id = $artist->artist_type;
        $artist->Is_also_comedian = (string) $artist->is_also_comedian;

        $artist->IsPublished = (int) $artist->is_published === 1
            ? 'YES'
            : 'NO';

        // Keep country_id numeric for refactored views.
        // Use this separate value in the existing country dropdown.
        $artist->country_key = match ((int) $artist->country_id) {
            1 => 'naija',
            2 => 'ghana',
            3 => 'african',
            default => '',
        };

        return $artist;
    }

    private function saveImage(UploadedFile $image): string
    {
        $directory = public_path('images');

        File::ensureDirectoryExists($directory);

        $filename = 'admin-artist-'
            . bin2hex(random_bytes(12))
            . '.'
            . $image->extension();

        $image->move($directory, $filename);

        return $filename;
    }

    private function deleteUploadedImage(?string $value): void
    {
        $filename = trim((string) $value);

        // Delete only files generated by this admin controller.
        if (
            !preg_match(
                '~^admin-artist-[a-f0-9]{24}\.(jpg|jpeg|png|webp|gif)$~i',
                $filename
            )
        ) {
            return;
        }

        File::delete(public_path('images/' . $filename));
    }

    private function rejectDuplicateName(
        string $name,
        ?int $exceptId = null
    ): void {
        $name = trim($name);
        $slug = Str::slug($name);

        $query = DB::table('artists')
            ->select('id', 'stage_name', 'slug');

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        foreach ($query->cursor() as $artist) {
            $existingName = trim((string) $artist->stage_name);

            if (
                mb_strtolower($existingName, 'UTF-8')
                    === mb_strtolower($name, 'UTF-8')
                || Str::slug($existingName) === $slug
                || trim((string) $artist->slug) === $slug
            ) {
                throw ValidationException::withMessages([
                    'Stage_Name' =>
                        'An artist with this name or URL already exists.',
                ]);
            }
        }
    }

    private function acquireWriteLock(): void
    {
        $locked = DB::selectOne(
            "SELECT GET_LOCK('trendybeatz_artist_write', 10) AS acquired"
        );

        if ((int) ($locked->acquired ?? 0) !== 1) {
            throw ValidationException::withMessages([
                'Stage_Name' =>
                    'Could not save the artist. Please try again.',
            ]);
        }
    }

    private function releaseWriteLock(): void
    {
        DB::select(
            "SELECT RELEASE_LOCK('trendybeatz_artist_write')"
        );
    }
}