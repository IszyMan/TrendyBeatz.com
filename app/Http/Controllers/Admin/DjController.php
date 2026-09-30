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

class DjController extends Controller
{
    private const COUNTRIES = [
        'naija' => 1,
        'ghana' => 2,
        'african' => 3,
    ];

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $djs = DB::table('djs')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%");

                    if (ctype_digit($search)) {
                        $query->orWhere('id', (int) $search);
                    }
                });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $djs->getCollection()->transform(
            fn ($dj) => $this->viewDj($dj)
        );

        return view('admin.djs.index', compact('djs', 'search'));
    }

    public function create()
    {
        return view('admin.djs.create', [
            'dj' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->acquireWriteLock();

        $filename = null;

        try {
            $this->rejectDuplicateName($data['dj_name']);

            $values = $this->djValues($data);

            if ($request->hasFile('photo_upload')) {
                $filename = $this->saveImage(
                    $request->file('photo_upload')
                );

                $values['profile_img'] = $filename;
            }

            $userId = (int) $request->user()->id;

            $values['user_id'] = $userId;
            $values['posted_by'] = in_array($userId, [4, 5], true)
                ? $userId
                : 5;

            $values['created_at'] = now();
            $values['updated_at'] = now();

            DB::table('djs')->insertGetId($values);
        } catch (\Throwable $exception) {
            if ($filename !== null) {
                File::delete(public_path('images/' . $filename));
            }

            throw $exception;
        } finally {
            $this->releaseWriteLock();
        }

        return redirect()
            ->route('admin.djs.index')
            ->with('success', 'DJ created.');
    }

    public function edit(int $dj)
    {
        $dj = DB::table('djs')
            ->where('id', $dj)
            ->firstOrFail();

        $dj = $this->viewDj($dj);

        return view('admin.djs.edit', compact('dj'));
    }

    public function update(Request $request, int $dj)
    {
        $row = DB::table('djs')
            ->where('id', $dj)
            ->firstOrFail();

        $data = $this->validated($request);
        $this->acquireWriteLock();

        $filename = null;

        try {
            $this->rejectDuplicateName(
                $data['dj_name'],
                (int) $row->id
            );

            $values = $this->djValues($data);
            $values['updated_at'] = now();

            if ($request->hasFile('photo_upload')) {
                $filename = $this->saveImage(
                    $request->file('photo_upload')
                );

                $values['profile_img'] = $filename;
            }

            // Preserve the creator, public author, creation date,
            // and existing image when no replacement is uploaded.
            DB::table('djs')
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
            $this->deleteUploadedImage($row->profile_img);
        }

        return redirect()
            ->route('admin.djs.index')
            ->with('success', 'DJ updated.');
    }

    public function destroy(int $dj)
    {
        $row = DB::table('djs')
            ->where('id', $dj)
            ->firstOrFail();

        if (
            DB::table('dj_mixs')
                ->where('dj_id', $row->id)
                ->exists()
        ) {
            return back()->withErrors([
                'dj' =>
                    'This DJ has mixtapes. Remove or reassign them first.',
            ]);
        }

        DB::table('djs')
            ->where('id', $row->id)
            ->delete();

        $this->deleteUploadedImage($row->profile_img);

        return redirect()
            ->route('admin.djs.index')
            ->with('success', 'DJ deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'dj_name' => ['required', 'string', 'max:191'],
            'fullname' => ['nullable', 'string', 'max:191'],
            'place_of_birth' => ['nullable', 'string'],
            'country_id' => [
                'nullable',
                Rule::in(array_keys(self::COUNTRIES)),
            ],
            'IsPublished' => [
                'required',
                Rule::in(['YES', 'NO']),
            ],
            'photo_upload' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:5120',
            ],
        ]);

        $data['dj_name'] = trim($data['dj_name']);

        if (
            $data['dj_name'] === ''
            || Str::slug($data['dj_name']) === ''
        ) {
            throw ValidationException::withMessages([
                'dj_name' =>
                    'Enter a DJ name that can be used in the profile URL.',
            ]);
        }

        return $data;
    }

    private function djValues(array $data): array
    {
        return [
            'name' => $data['dj_name'],
            'full_name' => $data['fullname'] ?? null,
            'slug' => Str::slug($data['dj_name']),
            'place_of_birth' => $data['place_of_birth'] ?? null,
            'country_id' => filled($data['country_id'] ?? null)
                ? self::COUNTRIES[$data['country_id']]
                : null,
            'is_published' => $data['IsPublished'] === 'YES'
                ? 1
                : 0,
        ];
    }

    /**
     * Supply aliases expected by the existing DJ form and index.
     * The actual database column names remain available too.
     */
    private function viewDj(object $dj): object
    {
        $dj->dj_name = $dj->name;
        $dj->fullname = $dj->full_name;
        $dj->photo = $dj->profile_img;

        $dj->IsPublished = (int) $dj->is_published === 1
            ? 'YES'
            : 'NO';

        $dj->country_key = match ((int) $dj->country_id) {
            1 => 'naija',
            2 => 'ghana',
            3 => 'african',
            default => '',
        };

        return $dj;
    }

    private function rejectDuplicateName(
        string $name,
        ?int $exceptId = null
    ): void {
        $name = trim($name);
        $slug = Str::slug($name);

        $query = DB::table('djs')
            ->select('id', 'name', 'slug');

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        foreach ($query->cursor() as $dj) {
            $existingName = trim((string) $dj->name);

            if (
                mb_strtolower($existingName, 'UTF-8')
                    === mb_strtolower($name, 'UTF-8')
                || Str::slug($existingName) === $slug
                || trim((string) $dj->slug) === $slug
            ) {
                throw ValidationException::withMessages([
                    'dj_name' =>
                        'A DJ with this name or profile URL already exists.',
                ]);
            }
        }
    }

    private function saveImage(UploadedFile $image): string
    {
        $directory = public_path('images');

        File::ensureDirectoryExists($directory);

        $filename = 'admin-dj-'
            . bin2hex(random_bytes(12))
            . '.'
            . $image->extension();

        $image->move($directory, $filename);

        return $filename;
    }

    private function deleteUploadedImage(?string $value): void
    {
        $filename = trim((string) $value);

        // Delete only files generated by this controller.
        if (!preg_match(
            '~^admin-dj-[a-f0-9]{24}\.(jpg|jpeg|png|webp|gif)$~i',
            $filename
        )) {
            return;
        }

        File::delete(public_path('images/' . $filename));
    }

    private function acquireWriteLock(): void
    {
        $locked = DB::selectOne(
            "SELECT GET_LOCK('trendybeatz_dj_write', 10) AS acquired"
        );

        if ((int) ($locked->acquired ?? 0) !== 1) {
            throw ValidationException::withMessages([
                'dj_name' =>
                    'Could not save the DJ. Please try again.',
            ]);
        }
    }

    private function releaseWriteLock(): void
    {
        DB::select(
            "SELECT RELEASE_LOCK('trendybeatz_dj_write')"
        );
    }
}