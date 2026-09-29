<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class AlbumController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $albums = DB::table('albums as al')
            ->leftJoin('artists as a', 'a.Artists_Id', '=', 'al.artist_id')
            ->select(
                'al.*',
                'a.Stage_Name',
                'a.ArtistsName'
            )
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('al.title', 'like', "%{$search}%")
                        ->orWhere('al.id', 'like', "%{$search}%")
                        ->orWhere('a.Stage_Name', 'like', "%{$search}%")
                        ->orWhere('a.ArtistsName', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('al.id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.albums.index', compact('albums', 'search'));
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
        $data = $this->validated($request);
        $filename = null;

        try {
            if ($request->hasFile('cover_image')) {
                $filename = $this->saveImage($request->file('cover_image'));
                $data['cover_url'] = $filename;
            }

            unset($data['cover_image']);

            $data['posted_by'] = $request->user()->id;
            $data['created_at'] = now();

            DB::table('albums')->insert($data);
        } catch (\Throwable $e) {
            if ($filename) {
                File::delete(public_path('images/' . $filename));
            }

            throw $e;
        }

        return redirect()
            ->route('admin.albums.index')
            ->with('success', 'Album created.');
    }

    public function edit(int $album)
    {
        $album = DB::table('albums')->where('id', $album)->firstOrFail();

        return view('admin.albums.edit', [
            'album' => $album,
            'artists' => $this->artists(),
        ]);
    }

    public function update(Request $request, int $album)
    {
        $row = DB::table('albums')->where('id', $album)->firstOrFail();
        $data = $this->validated($request);
        $filename = null;

        try {
            if ($request->hasFile('cover_image')) {
                $filename = $this->saveImage($request->file('cover_image'));
                $data['cover_url'] = $filename;
            }

            unset($data['cover_image']);

            DB::table('albums')
                ->where('id', $row->id)
                ->update($data);
        } catch (\Throwable $e) {
            if ($filename) {
                File::delete(public_path('images/' . $filename));
            }

            throw $e;
        }

        if ($filename && $row->cover_url) {
            File::delete(public_path('images/' . basename($row->cover_url)));
        }

        return redirect()
            ->route('admin.albums.index')
            ->with('success', 'Album updated.');
    }

    public function destroy(int $album)
    {
        $row = DB::table('albums')->where('id', $album)->firstOrFail();

        if (DB::table('listing')->where('album_id', $row->id)->exists()) {
            return back()->withErrors([
                'album' => 'This album has listings. Remove or reassign them first.',
            ]);
        }

        DB::table('albums')->where('id', $row->id)->delete();

        if ($row->cover_url) {
            File::delete(public_path('images/' . basename($row->cover_url)));
        }

        return redirect()
            ->route('admin.albums.index')
            ->with('success', 'Album deleted.');
    }

    private function artists()
    {
        return DB::table('artists')
            ->select('Artists_Id', 'Stage_Name', 'ArtistsName')
            ->orderBy('Stage_Name')
            ->get();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'artist_id' => [
                'required',
                'string',
                Rule::exists('artists', 'Artists_Id'),
            ],
            'title' => ['required', 'string', 'max:5000'],
            'featuring' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'released_year' => [
                'required',
                'regex:/^(19|20)\d{2}$/',
            ],
            'released_date' => ['nullable', 'date'],
            'IsPublished' => ['required', Rule::in(['YES', 'NO'])],
            'cover_image' => [
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

        $filename = 'admin-album-'
            . bin2hex(random_bytes(12))
            . '.'
            . $image->extension();

        $image->move($directory, $filename);

        // Store only the filename in albums.cover_url.
        return $filename;
    }
}