<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DjController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $djs = DB::table('dj')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('dj_name', 'like', "%{$search}%")
                        ->orWhere('fullname', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.djs.index', compact('djs', 'search'));
    }

    public function create()
    {
        return view('admin.djs.create', ['dj' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->rejectDuplicateName($data['dj_name']);

        $filename = null;

        try {
            if ($request->hasFile('photo_upload')) {
                $filename = $this->saveImage($request->file('photo_upload'));
                $data['photo'] = $filename;
            }

            unset($data['photo_upload']);

            $data['posted_by'] = $request->user()->id;
            $data['created_at'] = now();
            $data['updated_at'] = now()->toDateString();

            DB::table('dj')->insert($data);
        } catch (\Throwable $e) {
            if ($filename) {
                File::delete(public_path('images/' . $filename));
            }

            throw $e;
        }

        return redirect()
            ->route('admin.djs.index')
            ->with('success', 'DJ created.');
    }

    public function edit(int $dj)
    {
        $dj = DB::table('dj')->where('id', $dj)->firstOrFail();

        return view('admin.djs.edit', compact('dj'));
    }

    public function update(Request $request, int $dj)
    {
        $row = DB::table('dj')->where('id', $dj)->firstOrFail();
        $data = $this->validated($request);

        $this->rejectDuplicateName($data['dj_name'], $row->id);

        $filename = null;

        try {
            if ($request->hasFile('photo_upload')) {
                $filename = $this->saveImage($request->file('photo_upload'));
                $data['photo'] = $filename;
            }

            unset($data['photo_upload']);

            $data['updated_at'] = now()->toDateString();

            DB::table('dj')->where('id', $row->id)->update($data);
        } catch (\Throwable $e) {
            if ($filename) {
                File::delete(public_path('images/' . $filename));
            }

            throw $e;
        }

        if ($filename && $row->photo) {
            File::delete(public_path('images/' . basename($row->photo)));
        }

        return redirect()
            ->route('admin.djs.index')
            ->with('success', 'DJ updated.');
    }

    public function destroy(int $dj)
    {
        $row = DB::table('dj')->where('id', $dj)->firstOrFail();

        if (DB::table('dj_mixs')->where('dj_id', $row->id)->exists()) {
            return back()->withErrors([
                'dj' => 'This DJ has mixtapes. Remove or reassign them first.',
            ]);
        }

        DB::table('dj')->where('id', $row->id)->delete();

        if ($row->photo) {
            File::delete(public_path('images/' . basename($row->photo)));
        }

        return redirect()
            ->route('admin.djs.index')
            ->with('success', 'DJ deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'dj_name' => ['required', 'string', 'max:50'],
            'fullname' => ['nullable', 'string', 'max:50'],
            'place_of_birth' => ['nullable', 'string', 'max:10000'],
            'country_id' => [
                'nullable',
                Rule::in(['naija', 'ghana', 'african']),
            ],
            'IsPublished' => ['required', Rule::in(['YES', 'NO'])],
            'photo_upload' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:5120',
            ],
        ]);
    }

    private function rejectDuplicateName(
        string $name,
        ?int $exceptId = null
    ): void {
        $query = DB::table('dj')
            ->whereRaw('LOWER(TRIM(dj_name)) = LOWER(?)', [trim($name)]);

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'dj_name' => 'This DJ already exists.',
            ]);
        }
    }

    private function saveImage(\Illuminate\Http\UploadedFile $image): string
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
}