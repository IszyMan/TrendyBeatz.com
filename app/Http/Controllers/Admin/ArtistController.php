<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ArtistController extends Controller
{
    public function index()
    {
        $artists = DB::table('artists')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.artists.index', compact('artists'));
    }

    public function create()
    {
        return view('admin.artists.create', ['artist' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $this->rejectDuplicateName($data);

        $locked = DB::selectOne(
            "SELECT GET_LOCK('trendybeatz_artist_id', 10) AS acquired"
        );

        if ((int) $locked->acquired !== 1) {
            throw ValidationException::withMessages([
                'Stage_Name' => 'Could not assign an artist ID. Please try again.',
            ]);
        }

        $filename = null;

        try {
            // Check again inside the lock in case another admin just added the artist.
            $this->rejectDuplicateName($data);

            $lastNumber = DB::table('artists')
                ->where('Artists_Id', 'regexp', '^TB[0-9]+$')
                ->selectRaw(
                    'MAX(CAST(SUBSTRING(Artists_Id, 3) AS UNSIGNED)) AS number'
                )
                ->value('number');

            $data['Artists_Id'] = 'TB' . str_pad(
                (string) (((int) $lastNumber) + 1),
                3,
                '0',
                STR_PAD_LEFT
            );

            if ($request->hasFile('profile_image')) {
                $filename = $this->saveImage($request->file('profile_image'));
                $data['ProfilePic'] = $filename;
            }

            unset($data['profile_image']);

            $data['posteb_by'] = (string) $request->user()->id;
            $data['user_id'] = $request->user()->id;
            $data['created_at'] = now();
            $data['updated_at'] = now();

            DB::table('artists')->insert($data);
        } catch (\Throwable $e) {
            if ($filename) {
                File::delete(public_path('images/' . $filename));
            }

            throw $e;
        } finally {
            DB::select("SELECT RELEASE_LOCK('trendybeatz_artist_id')");
        }

        return redirect()
            ->route('admin.artists.index')
            ->with('success', 'Artist created.');
    }

    public function edit(int $artist)
    {
        $artist = DB::table('artists')->where('id', $artist)->first();

        abort_unless($artist, 404);

        return view('admin.artists.edit', compact('artist'));
    }

    public function update(Request $request, int $artist)
    {
        $row = DB::table('artists')->where('id', $artist)->first();

        abort_unless($row, 404);

        $data = $this->validated($request);
        $this->rejectDuplicateName($data, $row->id);
        $image = $request->file('profile_image');

        if ($image) {
            $data['ProfilePic'] = $this->saveImage($image);
        }

        unset($data['profile_image']);

        $data['updated_at'] = now();

        try {
            DB::table('artists')
                ->where('id', $row->id)
                ->update($data);
        } catch (\Throwable $e) {
            if ($image && isset($data['ProfilePic'])) {
                File::delete(public_path('images/' . $data['ProfilePic']));
            }

            throw $e;
        }

        if ($image && $row->ProfilePic) {
            File::delete(public_path('images/' . basename($row->ProfilePic)));
        }

        return redirect()
            ->route('admin.artists.index')
            ->with('success', 'Artist updated.');
    }

    public function destroy(int $artist)
    {
        $row = DB::table('artists')->where('id', $artist)->first();

        abort_unless($row, 404);

        if (
            DB::table('listing')
                ->where('Artists_Id', $row->Artists_Id)
                ->exists()
            || DB::table('albums')
                ->where('artist_id', $row->Artists_Id)
                ->exists()
        ) {
            return back()->withErrors([
                'artist' => 'This artist has listings or albums. Remove those first.',
            ]);
        }

        DB::table('artists')->where('id', $row->id)->delete();

        if ($row->ProfilePic) {
            File::delete(public_path('images/' . basename($row->ProfilePic)));
        }

        return redirect()
            ->route('admin.artists.index')
            ->with('success', 'Artist deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'ArtistsName' => ['nullable', 'string', 'max:100'],
            'Stage_Name' => ['required', 'string', 'max:100'],
            'Fullname' => ['nullable', 'string', 'max:100'],
            'ArtistsProfile' => ['nullable', 'string', 'max:100'],
            'RecordLabel' => ['nullable', 'string', 'max:100'],
            'Place_Birth' => ['nullable', 'string', 'max:10000'],
            'Genres' => ['nullable', 'string', 'max:100'],            
            'meta_keyword' => ['nullable', 'string', 'max:50'],
            'country_id' => ['required', Rule::in(['naija', 'ghana', 'african'])],
            'IsPublished' => ['required', Rule::in(['YES', 'NO'])],
            'category_id' => ['required', 'integer', 'min:0'],
            'order_id' => ['required', 'integer', 'min:0'],
            'Is_also_comedian' => ['required', Rule::in(['0', '1'])],
            'networth_id' => ['nullable', 'integer', 'min:0'],
            'profile_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:5120',
            ],
        ]);
    }

    private function saveImage(\Illuminate\Http\UploadedFile $image): string
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

    private function rejectDuplicateName(array $data, ?int $exceptId = null): void
    {
        foreach (['Stage_Name', 'ArtistsName'] as $field) {
            $name = trim((string) ($data[$field] ?? ''));

            if ($name === '') {
                continue;
            }

            $query = DB::table('artists')
                ->where(function ($query) use ($name) {
                    $query->whereRaw('LOWER(TRIM(Stage_Name)) = LOWER(?)', [$name])
                        ->orWhereRaw('LOWER(TRIM(ArtistsName)) = LOWER(?)', [$name]);
                });

            if ($exceptId !== null) {
                $query->where('id', '!=', $exceptId);
            }

            if ($query->exists()) {
                throw ValidationException::withMessages([
                    $field => 'This artist already exists.',
                ]);
            }
        }
    }
}