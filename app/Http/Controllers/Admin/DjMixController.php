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
        $idData = $request->validate([
            'mix_id' => [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('dj_mixs', 'id'),
            ],
        ], [
            'mix_id.unique' => 'This Mix ID already exists. Choose another ID.',
        ]);

        $data = $this->validated($request);

        // Validate the filename before moving uploaded images.
        $audioFilename = $this->audioFilename(
            $data['track_url'] ?? null
        );

        $values = $this->mixValues($data);

        if (filled($idData['mix_id'] ?? null)) {
            $values['id'] = (int) $idData['mix_id'];
        }

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

            $values['copyright_image'] = null;

            if ($request->hasFile('copyright_image')) {
                $filename = $this->saveCopyrightImage(
                    $request->file('copyright_image')
                );

                $values['copyright_image'] = $filename;

                // The existing catch block deletes uploads relative to images/.
                $uploaded[] = 'copyright/' . $filename;
            }

            // Numeric ID and counters use their database defaults.
            $mixId = DB::transaction(function () use ($values) {
                $id = DB::table('dj_mixs')->insertGetId($values);
            
                if ((int) $values['is_published'] === 1) {
                    ActivityLogger::record(
                        'published',
                        'dj mix',
                        $id,
                        $values['title']
                    );
                }
            
                return $id;
            });
        } catch (\Throwable $exception) {
            foreach ($uploaded as $filename) {
                File::delete(public_path('images/' . $filename));
            }

            // Handle an ID taken by another request after validation.
            if (
                $exception instanceof \Illuminate\Database\QueryException
                && (int) ($exception->errorInfo[1] ?? 0) === 1062
                && isset($values['id'])
                && DB::table('dj_mixs')->where('id', $values['id'])->exists()
            ) {
                throw ValidationException::withMessages([
                    'mix_id' => 'This Mix ID already exists. Choose another ID.',
                ]);
            }

            throw $exception;
        }

        return redirect()
            ->route('admin.dj-mixes.edit', ['dj_mix' => $mixId])
            ->with('success', 'DJ mix created Successfully.');
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

            if ($request->hasFile('copyright_image')) {
                $filename = $this->saveCopyrightImage(
                    $request->file('copyright_image')
                );

                $values['copyright_image'] = $filename;
                $uploaded[] = 'copyright/' . $filename;
            } elseif ($request->boolean('remove_copyright_image')) {
                $values['copyright_image'] = null;
            }

            // Preserve user_id, posted_by, created_at and counters.
            DB::transaction(function () use ($row, $values) {
                $before = DB::table('dj_mixs')
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
            
                DB::table('dj_mixs')
                    ->where('id', $before->id)
                    ->update($values);
            
                if ($changed) {
                    $publicationChanged =
                        (int) $before->is_published !== (int) $values['is_published'];
            
                    $action = $publicationChanged
                        ? ((int) $values['is_published'] === 1
                            ? 'published'
                            : 'unpublished')
                        : 'updated';
            
                    ActivityLogger::record(
                        $action,
                        'dj mix',
                        $before->id,
                        $values['title']
                    );
                }
            });
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
            ->route('admin.dj-mixes.edit', ['dj_mix' => $dj_mix])
            ->with('success', 'DJ mix updated Successfully.');
    }

    public function destroy(int $dj_mix)
    {
        $row = DB::table('dj_mixs')
            ->where('id', $dj_mix)
            ->firstOrFail();

        DB::transaction(function () use ($row) {
            $deleted = DB::table('dj_mixs')
                ->where('id', $row->id)
                ->delete();
        
            if ($deleted) {
                ActivityLogger::record(
                    'deleted',
                    'dj mix',
                    $row->id,
                    $row->title
                );
            }
        });

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
            'audiomack_embed_url' => ['nullable', 'string', 'max:5000'],
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

            'copyright_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'remove_copyright_image' => ['nullable', 'boolean'],
        ]);

        $data['mix_title'] = trim($data['mix_title']);

        if ($data['mix_title'] === '') {
            throw ValidationException::withMessages([
                'mix_title' => 'Enter a DJ mix title.',
            ]);
        }
        
        $data['audiomack_embed_url'] = $this->audiomackEmbedUrl(
            $data['audiomack_embed_url'] ?? null
        );

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
            'audiomack_embed_url' => $data['audiomack_embed_url'] ?? null,
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

        // Keep images still used by another record.
        if (
            DB::table('dj_mixs')
                ->where(function ($query) use ($filename) {
                    $query->where('cover_url', $filename)
                        ->orWhere('back_cover', $filename);
                })
                ->exists()
            || DB::table('djs')->where('profile_img', $filename)->exists()
            || DB::table('artists')->where('img', $filename)->exists()
            || DB::table('albums')->where('cover_url', $filename)->exists()
            || DB::table('listings')->where('cover_url', $filename)->exists()
        ) {
            return;
        }

        File::delete(public_path('images/' . $filename));
    }


    private function saveCopyrightImage(UploadedFile $file): string
    {
        $directory = public_path('images/copyright');

        File::ensureDirectoryExists($directory);

        $originalName = pathinfo(
            $file->getClientOriginalName(),
            PATHINFO_FILENAME
        );

        $baseName = Str::limit(
            Str::slug($originalName) ?: 'copyright-permission',
            140,
            ''
        );

        $extension = $file->extension();
        $filename = $baseName . '.' . $extension;
        $counter = 1;

        while (File::exists($directory . DIRECTORY_SEPARATOR . $filename)) {
            $filename = $baseName . '-' . $counter . '.' . $extension;
            $counter++;
        }

        $file->move($directory, $filename);

        return $filename;
    }
    
    
    private function audiomackEmbedUrl(?string $value): ?string
    {
        $value = trim((string) $value);
    
        if ($value === '') {
            return null;
        }
    
        $error = [
            'audiomack_embed_url' =>
                'Paste a valid Audiomack embed iframe.',
        ];
    
        $document = new \DOMDocument();
    
        $previous = libxml_use_internal_errors(true);
    
        try {
            $loaded = $document->loadHTML(
                '<?xml encoding="UTF-8">' . $value,
                LIBXML_NONET
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    
        if (!$loaded) {
            throw ValidationException::withMessages($error);
        }
    
        $iframes = $document->getElementsByTagName('iframe');
    
        if ($iframes->length !== 1) {
            throw ValidationException::withMessages($error);
        }
    
        $url = trim($iframes->item(0)->getAttribute('src'));
        $parts = parse_url($url);
    
        if (
            !filter_var($url, FILTER_VALIDATE_URL)
            || $parts === false
            || strtolower($parts['scheme'] ?? '') !== 'https'
            || !in_array(
                strtolower($parts['host'] ?? ''),
                ['audiomack.com', 'www.audiomack.com'],
                true
            )
            || !str_starts_with($parts['path'] ?? '', '/embed/')
            || strlen($parts['path'] ?? '') <= strlen('/embed/')
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['port'])
        ) {
            throw ValidationException::withMessages($error);
        }
    
        return $url;
    }
}