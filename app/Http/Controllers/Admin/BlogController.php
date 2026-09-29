<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $blogs = DB::table('blogs as b')
            ->leftJoin('blog_types as c', 'c.id', '=', 'b.category_id')
            ->leftJoin('users as u', 'u.id', '=', 'b.posted_by')
            ->select(
                'b.*',
                'c.name as category_name',
                'u.name as public_author_name'
            )
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('b.title', 'like', "%{$search}%")
                        ->orWhere('c.name', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('b.id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.blogs.index', compact('blogs', 'search'));
    }

    public function create()
    {
        return view('admin.blogs.create', [
            'blog' => null,
            'categories' => $this->categories(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $filename = null;

        if ($request->hasFile('photo_upload')) {
            $filename = $this->saveImage($request->file('photo_upload'));
            $data['photo'] = $filename;
        } else {
            // The legacy photo column is NOT NULL.
            $data['photo'] = '';
        }

        unset($data['photo_upload']);

        $locked = DB::selectOne(
            "SELECT GET_LOCK('trendybeatz_blog_author_rotation', 10) AS acquired"
        );

        if ((int) $locked->acquired !== 1) {
            if ($filename) {
                File::delete(public_path('images/' . $filename));
            }

            throw ValidationException::withMessages([
                'title' => 'Could not save the post. Please try again.',
            ]);
        }

        try {
            DB::transaction(function () use ($request, $data) {
                $lastAuthor = DB::table('blogs')
                    ->whereIn('posted_by', ['4', '5'])
                    ->orderByDesc('id')
                    ->value('posted_by');

                $data['posted_by'] = (string) $lastAuthor === '4'
                    ? '5'
                    : '4';

                $data['user_id'] = $request->user()->id;
                $data['slug'] = Str::slug($data['title']);
                $data['created_at'] = now();
                $data['updated_at'] = now();

                DB::table('blogs')->insert($data);
            });
        } catch (\Throwable $e) {
            if ($filename) {
                File::delete(public_path('images/' . $filename));
            }

            throw $e;
        } finally {
            DB::select(
                "SELECT RELEASE_LOCK('trendybeatz_blog_author_rotation')"
            );
        }

        return redirect()
            ->route('admin.blogs.index')
            ->with('success', 'Blog post created.');
    }

    public function edit(int $blog)
    {
        $blog = DB::table('blogs')->where('id', $blog)->firstOrFail();

        return view('admin.blogs.edit', [
            'blog' => $blog,
            'categories' => $this->categories(),
        ]);
    }

    public function update(Request $request, int $blog)
    {
        $row = DB::table('blogs')->where('id', $blog)->firstOrFail();
        $data = $this->validated($request);
        $filename = null;

        try {
            if ($request->hasFile('photo_upload')) {
                $filename = $this->saveImage($request->file('photo_upload'));
                $data['photo'] = $filename;
            }

            unset($data['photo_upload']);

            $data['slug'] = Str::slug($data['title']);
            $data['updated_at'] = now();

            // posted_by and user_id remain the original values.
            DB::table('blogs')->where('id', $row->id)->update($data);
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
            ->route('admin.blogs.index')
            ->with('success', 'Blog post updated.');
    }

    public function destroy(int $blog)
    {
        $row = DB::table('blogs')->where('id', $blog)->firstOrFail();

        DB::table('blogs')->where('id', $row->id)->delete();

        if ($row->photo) {
            File::delete(public_path('images/' . basename($row->photo)));
        }

        return redirect()
            ->route('admin.blogs.index')
            ->with('success', 'Blog post deleted.');
    }

    private function categories()
    {
        return DB::table('blog_types')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'category_id' => [
                'required',
                'integer',
                Rule::exists('blog_types', 'id'),
            ],
            'title' => ['required', 'string', 'max:1000'],
            'intro' => ['required', 'string'],
            'description' => ['required', 'string'],
            'scriptUrl' => ['nullable', 'string'],
            'IsPublished' => ['required', Rule::in(['YES', 'NO'])],
            'photo_upload' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:4096',
            ],
        ]);
    }

    private function saveImage(\Illuminate\Http\UploadedFile $image): string
    {
        $directory = public_path('images');

        File::ensureDirectoryExists($directory);

        $filename = 'admin-blog-'
            . bin2hex(random_bytes(12))
            . '.'
            . $image->extension();

        $image->move($directory, $filename);

        return $filename;
    }
}