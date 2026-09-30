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

class DjMixController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $mixes = DB::table('dj_mixs as mix')
            ->leftJoin(
                'djs as dj',
                'dj.id',
                '=',
                'mix.dj_id'
            )
            ->select(
                'mix.*',
                'dj.name as dj_name'
            )
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('mix.title', 'like', "%{$search}%")
                        ->orWhere('dj.name', 'like', "%{$search}%")
                        ->orWhere('dj.full_name', 'like', "%{$search}%");

                    if (ctype_digit($search)) {
                        $query->orWhere('mix.id', (int) $search);
                    }
                });
            })
            ->orderByDesc('mix.id')
            ->paginate(20)
            ->withQueryString();

        $mixes->getCollection()->transform(
            fn ($mix) => $this->viewMix($mix)
        );

        return view(
            'admin.dj-mixes.index',
            compact('mixes', 'search')
        );
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

        // Validate the filename before moving uploaded images.
        $audioFilename = $this->audioFilename(
            $data['track_url'] ?? null
        );

        $values = $this->mixValues($data);
        $values['track_url'] = $audioFilename;

        $userId = (int) $request->user()->id;

        $values['user_id'] = $userId;
        $values['posted_by'] = in_array($userId, [4, 5], true)
            ? $userId
            : 5;

        $values['created_at'] = now();
        $values['updated_at'] = now();

        $uploaded = [];

        try {
            if ($request->hasFile('cover_image')) {
                $filename = $this->saveImage(
                    $request->file('cover_image')
                );

                $uploaded[] = $filename;
                $values['cover_url'] = $filename;
            }

            if ($request->hasFile('back_cover_image')) {
                $filename = $this->saveImage(
                    $request->file('back_cover_image')
                );

                $uploaded[] = $filename;
                $values['back_cover'] = $filename;
            }

            // Numeric ID and counters use their database defaults.
            DB::table('dj_mixs')->insertGetId($values);
        } catch (\Throwable $exception) {
            foreach ($uploaded as $filename) {
                File::delete(public_path('images/' . $filename));
            }

            throw $exception;
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
            'mix' => $this->viewMix($mix),
            'djs' => $this->djs(),
        ]);
    }

    public function update(Request $request, int $dj_mix)
    {
        $row = DB::table('dj_mixs')
            ->where('id', $dj_mix)
            ->firstOrFail();

        $data = $this->validated($request);

        $audioFilename = $this->audioFilename(
            $data['track_url'] ?? null
        );

        $values = $this->mixValues($data);
        $values['updated_at'] = now();

        // A blank audio field keeps the existing filename.
        if ($audioFilename !== null) {
            $values['track_url'] = $audioFilename;
        }

        $uploaded = [];

        try {
            if ($request->hasFile('cover_image')) {
                $filename = $this->saveImage(
                    $request->file('cover_image')
                );

                $uploaded[] = $filename;
                $values['cover_url'] = $filename;
            }

            if ($request->hasFile('back_cover_image')) {
                $filename = $this->saveImage(
                    $request->file('back_cover_image')
                );

                $uploaded[] = $filename;
                $values['back_cover'] = $filename;
            }

            // Preserve user_id, posted_by, created_at and counters.
            DB::table('dj_mixs')
                ->where('id', $row->id)
                ->update($values);
        } catch (\Throwable $exception) {
            foreach ($uploaded as $filename) {
                File::delete(public_path('images/' . $filename));
            }

            throw $exception;
        }

        if (isset($values['cover_url'])) {
            $this->deleteUploadedImage($row->cover_url);
        }

        if (isset($values['back_cover'])) {
            $this->deleteUploadedImage($row->back_cover);
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

        DB::table('dj_mixs')
            ->where('id', $row->id)
            ->delete();

        foreach ([$row->cover_url, $row->back_cover] as $filename) {
            $this->deleteUploadedImage($filename);
        }

        // Keep the externally stored audio file.
        return redirect()
            ->route('admin.dj-mixes.index')
            ->with('success', 'DJ mix deleted.');
    }

    private function djs()
    {
        return DB::table('djs')
            ->select(
                'id',
                'name',
                'full_name',
                'name as dj_name'
            )
            ->orderBy('name')
            ->get();
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'dj_id' => [
                'required',
                'integer',
                Rule::exists('djs', 'id'),
            ],
            'mix_title' => ['required', 'string', 'max:191'],
            'introduction' => ['sometimes', 'nullable', 'string'],
            'details' => ['nullable', 'string'],
            'details2' => ['nullable', 'string'],
            'description1' => ['nullable', 'string'],
            'description2' => ['nullable', 'string'],
            'released_year' => [
                'nullable',
                'regex:/^(19|20)\d{2}$/',
            ],
            'track_url' => ['nullable', 'string', 'max:191'],
            'IsPublished' => [
                'required',
                Rule::in(['YES', 'NO']),
            ],
            'cover_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:5120',
            ],
            'back_cover_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:5120',
            ],
        ]);

        $data['mix_title'] = trim($data['mix_title']);

        if ($data['mix_title'] === '') {
            throw ValidationException::withMessages([
                'mix_title' => 'Enter a DJ mix title.',
            ]);
        }

        return $data;
    }

    private function mixValues(array $data): array
    {
        $dj = DB::table('djs')
            ->select('name')
            ->where('id', (int) $data['dj_id'])
            ->firstOrFail();

        $djName = trim((string) $dj->name);

        if ($djName === '') {
            $djName = 'TrendyBeatz DJ';
        }

        $values = [
            'dj_id' => (int) $data['dj_id'],
            'title' => $data['mix_title'],
            'slug' => Str::slug(
                $djName . ' ' . $data['mix_title']
            ),
            'details' => $data['details'] ?? null,
            'details2' => $data['details2'] ?? null,
            'description1' => $data['description1'] ?? null,
            'description2' => $data['description2'] ?? null,
            'released_year' => $data['released_year'] ?? null,
            'is_published' => $data['IsPublished'] === 'YES'
                ? 1
                : 0,
        ];

        // Preserve recovered introductions if the form omits this field.
        if (array_key_exists('introduction', $data)) {
            $values['introduction'] = $data['introduction'];
        }

        return $values;
    }

    private function viewMix(object $mix): object
    {
        $mix->mix_title = $mix->title;

        $mix->IsPublished = (int) $mix->is_published === 1
            ? 'YES'
            : 'NO';

        return $mix;
    }

    private function audioFilename(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (
            $value === '.'
            || $value === '..'
            || str_contains($value, '/')
            || str_contains($value, '\\')
            || preg_match('/[\x00-\x1F\x7F]/', $value)
            || mb_strlen($value, 'UTF-8') > 191
        ) {
            throw ValidationException::withMessages([
                'track_url' =>
                    'Enter only the audio filename, without a URL or folder. '
                    . 'The filename must not exceed 191 characters.',
            ]);
        }

        return $value;
    }

    private function saveImage(UploadedFile $image): string
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

    private function deleteUploadedImage(?string $value): void
    {
        $filename = trim((string) $value);

        // Delete only images generated by this controller.
        if (!preg_match(
            '~^admin-mix-[a-f0-9]{24}\.(jpg|jpeg|png|webp|gif)$~i',
            $filename
        )) {
            return;
        }

        File::delete(public_path('images/' . $filename));
    }
}