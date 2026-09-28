<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_types', function (Blueprint $table) {
            $table->string('slug', 100)->nullable()->after('name');
        });

        Schema::table('blogs', function (Blueprint $table) {
            $table->string('slug', 255)->nullable()->after('title');
        });

        // Preserve the existing IDs used by blogs.category_id.
        $existingCategories = [
            1 => 'sport-news',
            2 => 'celebrity-news',
            6 => 'hot-gists',
            7 => 'networth',
            8 => 'music-reviews',
        ];

        foreach ($existingCategories as $id => $slug) {
            DB::table('blog_types')
                ->where('id', $id)
                ->update(['slug' => $slug]);
        }

        $newCategories = [
            ['name' => 'Education', 'slug' => 'education'],
            ['name' => 'Articles', 'slug' => 'articles'],
            ['name' => 'News', 'slug' => 'news'],
        ];

        foreach ($newCategories as $category) {
            DB::table('blog_types')->insert($category);
        }

        // Use the existing titles to give imported posts working URLs.
        // Add the ID when a title produces a duplicate slug.
        DB::table('blogs')
            ->select('id', 'title')
            ->orderBy('id')
            ->chunkById(200, function ($blogs): void {
                foreach ($blogs as $blog) {
                    $baseSlug = Str::limit(
                        Str::slug($blog->title),
                        230,
                        ''
                    );

                    if ($baseSlug === '') {
                        $baseSlug = 'blog';
                    }

                    $slug = $baseSlug;

                    if (
                        DB::table('blogs')
                            ->where('slug', $slug)
                            ->exists()
                    ) {
                        $slug = $baseSlug . '-' . $blog->id;
                    }

                    DB::table('blogs')
                        ->where('id', $blog->id)
                        ->update(['slug' => $slug]);
                }
            });

        Schema::table('blog_types', function (Blueprint $table) {
            $table->unique('slug');
        });

        Schema::table('blogs', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });

        Schema::table('blog_types', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });

        DB::table('blog_types')
            ->whereIn('name', ['Education', 'Articles', 'News'])
            ->delete();
    }
};