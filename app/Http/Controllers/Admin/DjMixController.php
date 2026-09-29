<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DjMixController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $mixes = DB::table('dj_mixs as m')
            ->leftJoin('dj as d', 'd.id', '=', 'm.dj_id')
            ->select('m.*', 'd.dj_name')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('m.mix_title', 'like', "%{$search}%")
                        ->orWhere('d.dj_name', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('m.id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.dj-mixes.index', compact('mixes', 'search'));
    }

    public function create()
    {
        return view('admin.dj-mixes.create', [
            'mix' => null,
            'djs' => $this->djs(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $uploaded = [];

        try {
            if ($request->hasFile('cover_image')) {
                $data['cover_url'] = $this->saveImage(
                    $request->file('cover_image')
                );
                $uploaded[] = $data['cover_url'];
            }

            if ($request->hasFile('back_cover_image')) {
                $data['back_cover'] = $this->saveImage(
                    $request->file('back_cover_image')
                );
                $uploaded[] = $data['back_cover'];
            }

            unset($data['cover_image'], $data['back_cover_image']);

            $data['track_url'] = $this->audioFilename(
                $data['track_url'] ?? null
            );

            $data['slug'] = Str::slug($data['mix_title']);
            $data['posted_by'] = $request->user()->id;
            $data['created_at'] = now();

            // mix_count and stream_count use their database defaults.
            DB::table('dj_mixs')->insert($data);
        } catch (\Throwable $e) {
            foreach ($uploaded as $filename) {
                File::delete(public_path('images/' . $filename));
            }

            throw $e;
        }

        return redirect()
            ->route('admin.dj-mixes.index')
            ->with('success', 'DJ mix created.');
    }

    public function edit(int $dj_mix)
    {
        $mix = DB::table('dj_mixs')
            ->where('id', $dj_mix)
            ->firstOrFail();

        return view('admin.dj-mixes.edit', [
            'mix' => $mix,
            'djs' => $this->djs(),
        ]);
    }

    public function update(Request $request, int $dj_mix)
    {
        $row = DB::table('dj_mixs')
            ->where('id', $dj_mix)
            ->firstOrFail();

        $data = $this->validated($request);
        $uploaded = [];

        try {
            if ($request->hasFile('cover_image')) {
                $data['cover_url'] = $this->saveImage(
                    $request->file('cover_image')
                );
                $uploaded[] = $data['cover_url'];
            }

            if ($request->hasFile('back_cover_image')) {
                $data['back_cover'] = $this->saveImage(
                    $request->file('back_cover_image')
                );
                $uploaded[] = $data['back_cover'];
            }

            unset($data['cover_image'], $data['back_cover_image']);

            $data['track_url'] = $this->audioFilename(
                $data['track_url'] ?? null
            );

            $data['slug'] = Str::slug($data['mix_title']);

            DB::table('dj_mixs')
                ->where('id', $row->id)
                ->update($data);
        } catch (\Throwable $e) {
            foreach ($uploaded as $filename) {
                File::delete(public_path('images/' . $filename));
            }

            throw $e;
        }

        if (
            isset($data['cover_url'])
            && $row->cover_url
        ) {
            File::delete(public_path('images/' . basename($row->cover_url)));
        }

        if (
            isset($data['back_cover'])
            && $row->back_cover
        ) {
            File::delete(public_path('images/' . basename($row->back_cover)));
        }

        return redirect()
            ->route('admin.dj-mixes.index')
            ->with('success', 'DJ mix updated.');
    }

    public function destroy(int $dj_mix)
    {
        $row = DB::table('dj_mixs')
            ->where('id', $dj_mix)
            ->firstOrFail();

        DB::table('dj_mixs')->where('id', $row->id)->delete();

        foreach ([$row->cover_url, $row->back_cover] as $filename) {
            if ($filename) {
                File::delete(public_path('images/' . basename($filename)));
            }
        }

        // The audio file lives outside this image directory and is not deleted.
        return redirect()
            ->route('admin.dj-mixes.index')
            ->with('success', 'DJ mix deleted.');
    }

    private function djs()
    {
        return DB::table('dj')
            ->select('id', 'dj_name')
            ->orderBy('dj_name')
            ->get();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'dj_id' => [
                'required',
                'integer',
                Rule::exists('dj', 'id'),
            ],
            'mix_title' => ['required', 'string', 'max:150'],
            'details' => ['nullable', 'string', 'max:1000'],
            'details2' => ['nullable', 'string', 'max:1000'],
            'description1' => ['nullable', 'string'],
            'description2' => ['nullable', 'string'],
            'released_year' => [
                'nullable',
                'regex:/^(19|20)\d{2}$/',
            ],
            'track_url' => ['nullable', 'string', 'max:100'],
            'IsPublished' => ['required', Rule::in(['YES', 'NO'])],
            'cover_image' => [
                'nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120',
            ],
            'back_cover_image' => [
                'nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120',
            ],
        ]);
    }

    private function audioFilename(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        // Reject paths and URLs: track_url stores a filename only.
        if (
            $value === '.'
            || $value === '..'
            || str_contains($value, '/')
            || str_contains($value, '\\')
            || preg_match('/[\x00-\x1F\x7F]/', $value)
        ) {
            throw ValidationException::withMessages([
                'track_url' => 'Enter only the audio filename, without a URL or folder.',
            ]);
        }

        return $value;
    }

    private function saveImage(\Illuminate\Http\UploadedFile $image): string
    {
        $directory = public_path('images');

        File::ensureDirectoryExists($directory);

        $filename = 'admin-mix-'
            . bin2hex(random_bytes(12))
            . '.'
            . $image->extension();

        $image->move($directory, $filename);

        return $filename;
    }
}