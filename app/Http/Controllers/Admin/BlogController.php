<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Support\ActivityLogger;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $blogs = DB::table('blogs as b')
            ->leftJoin('blog_types as c', 'c.id', '=', 'b.blog_type')
            ->leftJoin('users as u', 'u.id', '=', 'b.posted_by')
            ->select(
                'b.*',
                'b.blog_type as category_id',
                'b.script_url as scriptUrl',
                DB::raw("
                    CASE WHEN b.Is_published = 1
                        THEN 'YES' ELSE 'NO'
                    END AS IsPublished
                "),
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
                File::delete(public_path('images/blog/' . $filename));
            }

            throw ValidationException::withMessages([
                'title' => 'Could not save the post. Please try again.',
            ]);
        }

        try {
            $blogId = DB::transaction(function () use ($request, $data) {
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

                $id = DB::table('blogs')->insertGetId($data);

                if ((int) $data['Is_published'] === 1) {
                    ActivityLogger::record(
                        'published',
                        'blog',
                        $id,
                        $data['title']
                    );
                }
                
                return $id;
            });
        } catch (\Throwable $e) {
            if ($filename) {
                File::delete(public_path('images/blog/' . $filename));
            }

            throw $e;
        } finally {
            DB::select(
                "SELECT RELEASE_LOCK('trendybeatz_blog_author_rotation')"
            );
        }

        return redirect()
            ->route('admin.blogs.edit', ['blog' => $blogId])
            ->with('success', 'Blog post created Successfully.');
    }

    public function edit(int $blog)
    {
        $blog = DB::table('blogs')
            ->select(
                'blogs.*',
                'blog_type as category_id',
                'script_url as scriptUrl',
                DB::raw("
                    CASE WHEN Is_published = 1
                        THEN 'YES' ELSE 'NO'
                    END AS IsPublished
                ")
            )
            ->where('id', $blog)
            ->firstOrFail();

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
            DB::transaction(function () use ($row, $data) {
                $before = DB::table('blogs')
                    ->where('id', $row->id)
                    ->lockForUpdate()
                    ->firstOrFail();
            
                $changed = false;
            
                foreach ($data as $field => $value) {
                    if ($field === 'updated_at') {
                        continue;
                    }
            
                    if ((string) ($before->{$field} ?? '') !== (string) ($value ?? '')) {
                        $changed = true;
                        break;
                    }
                }
            
                DB::table('blogs')
                    ->where('id', $before->id)
                    ->update($data);
            
                if ($changed) {
                    $publicationChanged =
                        (int) $before->Is_published !== (int) $data['Is_published'];
            
                    $action = $publicationChanged
                        ? ((int) $data['Is_published'] === 1
                            ? 'published'
                            : 'unpublished')
                        : 'updated';
            
                    ActivityLogger::record(
                        $action,
                        'blog',
                        $before->id,
                        $data['title']
                    );
                }
            });
        } catch (\Throwable $e) {
            if ($filename) {
                File::delete(public_path('images/blog/' . $filename));
            }

            throw $e;
        }

        if ($filename && $row->photo) {
            File::delete(
                public_path('images/blog/' . basename($row->photo))
            );
        }

        return redirect()
            ->route('admin.blogs.edit', ['blog' => $blog])
            ->with('success', 'Blog post updated Successfully.');
    }

    public function destroy(int $blog)
    {
        $row = DB::table('blogs')->where('id', $blog)->firstOrFail();

        DB::transaction(function () use ($row) {
            $deleted = DB::table('blogs')
                ->where('id', $row->id)
                ->delete();
        
            if ($deleted) {
                ActivityLogger::record(
                    'deleted',
                    'blog',
                    $row->id,
                    $row->title
                );
            }
        });

        if ($row->photo) {
            File::delete(public_path('images/blog/' . basename($row->photo)));
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
        $data = $request->validate([
            'category_id' => [
                'required',
                'integer',
                Rule::exists('blog_types', 'id'),
            ],
            'title' => ['required', 'string', 'max:1000'],
            'intro' => ['required', 'string'],
            'description' => ['required', 'string'],
            'scriptUrl' => ['nullable', 'string'],
            'script_url2' => ['nullable', 'string'],
            'hide_from_source' => ['nullable', 'boolean'],
            'IsPublished' => ['required', Rule::in(['YES', 'NO'])],
            'photo_upload' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:4096',
            ],
        ]);

        $data['hide_from_source'] = $request->boolean('hide_from_source') ? 1 : 0;

        $data['blog_type'] = $data['category_id'];
        $data['script_url'] = $data['scriptUrl'] ?? null;
        $data['script_url2'] = $data['script_url2'] ?? null;
        $data['Is_published'] = $data['IsPublished'] === 'YES' ? 1 : 0;

        unset(
            $data['category_id'],
            $data['scriptUrl'],
            $data['IsPublished']
        );

        return $data;
    }

    private function saveImage(\Illuminate\Http\UploadedFile $image): string
    {
        $directory = public_path('images/blog');

        File::ensureDirectoryExists($directory);

        $originalName = basename(
            str_replace('\\', '/', $image->getClientOriginalName())
        );

        $baseName = Str::slug(
            pathinfo($originalName, PATHINFO_FILENAME)
        ) ?: 'image';

        $extension = strtolower($image->extension());
        $filename = $baseName . '.' . $extension;

        if (File::exists($directory . '/' . $filename)) {
            throw ValidationException::withMessages([
                'photo_upload' =>
                    'An image with this filename already exists. Rename the image before uploading.',
            ]);
        }

        $image->move($directory, $filename);

        return $filename;
    }
}