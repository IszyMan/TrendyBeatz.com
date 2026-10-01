<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ListingController;
use App\Http\Controllers\Admin\ArtistController;
use App\Http\Controllers\Admin\AlbumController;
use App\Http\Controllers\Admin\DjController;
use App\Http\Controllers\Admin\DjMixController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\FeaturedController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get(
        '/admin/login',
        [AdminAuthController::class, 'showLogin']
    )->name('login');

    Route::post(
        '/admin/login',
        [AdminAuthController::class, 'login']
    )
        ->middleware('throttle:5,1')
        ->name('admin.login.submit');
});

/*
|--------------------------------------------------------------------------
| Dashboard and create/edit access
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware([
        'auth',
        'admin.role:administrator,standard,editor',
    ])
    ->group(function () {
        Route::get(
            '/',
            [AdminDashboardController::class, 'index']
        )->name('dashboard');

        Route::post(
            '/logout',
            [AdminAuthController::class, 'logout']
        )->name('logout');

        // Administrators and Standard users: create and edit content.
        Route::middleware('admin.role:administrator,standard')
            ->group(function () {
                Route::get(
                    '/listings',
                    [ListingController::class, 'index']
                )->name('listings.index');

                Route::get(
                    '/listings/create',
                    [ListingController::class, 'create']
                )->name('listings.create');

                Route::post(
                    '/listings',
                    [ListingController::class, 'store']
                )->name('listings.store');

                Route::get(
                    '/listings/{listing}/edit',
                    [ListingController::class, 'edit']
                )
                    ->whereNumber('listing')
                    ->name('listings.edit');

                Route::put(
                    '/listings/{listing}',
                    [ListingController::class, 'update']
                )
                    ->whereNumber('listing')
                    ->name('listings.update');

                Route::get(
                    '/artists/{artist}/albums',
                    [ListingController::class, 'artistAlbums']
                )->name('artists.albums');

                Route::resource('artists', ArtistController::class)
                    ->except(['show', 'destroy'])
                    ->names('artists');

                Route::resource('albums', AlbumController::class)
                    ->except(['show', 'destroy'])
                    ->names('albums');

                Route::resource('djs', DjController::class)
                    ->except(['show', 'destroy'])
                    ->names('djs');

                Route::resource('dj-mixes', DjMixController::class)
                    ->except(['show', 'destroy'])
                    ->names('dj-mixes');
            });

        // Administrators, Standard users and Editors: create and edit blogs.
            Route::middleware('admin.role:administrator,standard,editor')
                ->group(function () {
                    Route::resource('blogs', BlogController::class)
                        ->except(['show', 'destroy'])
                        ->names('blogs');
                });
    });

/*
|--------------------------------------------------------------------------
| Administrator-only moderation and deletion
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware([
        'auth',
        'admin.role:administrator',
    ])
    ->group(function () {
        Route::get(
            '/comments',
            [AdminCommentController::class, 'index']
        )->name('comments.index');

        Route::patch(
            '/comments/{comment}/approve',
            [AdminCommentController::class, 'approve']
        )
            ->whereNumber('comment')
            ->name('comments.approve');

        Route::delete(
            '/comments/{comment}',
            [AdminCommentController::class, 'destroy']
        )
            ->whereNumber('comment')
            ->name('comments.destroy');

        Route::delete(
            '/listings/{listing}',
            [ListingController::class, 'destroy']
        )
            ->whereNumber('listing')
            ->name('listings.destroy');

        Route::delete(
            '/artists/{artist}',
            [ArtistController::class, 'destroy']
        )
            ->whereNumber('artist')
            ->name('artists.destroy');

        Route::delete(
            '/albums/{album}',
            [AlbumController::class, 'destroy']
        )
            ->whereNumber('album')
            ->name('albums.destroy');

        Route::delete(
            '/djs/{dj}',
            [DjController::class, 'destroy']
        )
            ->whereNumber('dj')
            ->name('djs.destroy');

        Route::delete(
            '/dj-mixes/{dj_mix}',
            [DjMixController::class, 'destroy']
        )
            ->whereNumber('dj_mix')
            ->name('dj-mixes.destroy');

        Route::delete(
            '/blogs/{blog}',
            [BlogController::class, 'destroy']
        )
            ->whereNumber('blog')
            ->name('blogs.destroy');
    });

/*
|--------------------------------------------------------------------------
| Featured items
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        foreach ([
            'featured-songs' => 'song',
            'featured-albums' => 'album',
        ] as $path => $kind) {
            Route::get(
                $path,
                [FeaturedController::class, 'index']
            )
                ->defaults('kind', $kind)
                ->name($path . '.index');

            Route::get(
                $path . '/create',
                [FeaturedController::class, 'create']
            )
                ->defaults('kind', $kind)
                ->name($path . '.create');

            Route::post(
                $path,
                [FeaturedController::class, 'store']
            )
                ->defaults('kind', $kind)
                ->name($path . '.store');

            Route::get(
                $path . '/{id}/edit',
                [FeaturedController::class, 'edit']
            )
                ->whereNumber('id')
                ->defaults('kind', $kind)
                ->name($path . '.edit');

            Route::put(
                $path . '/{id}',
                [FeaturedController::class, 'update']
            )
                ->whereNumber('id')
                ->defaults('kind', $kind)
                ->name($path . '.update');

            Route::delete(
                $path . '/{id}',
                [FeaturedController::class, 'destroy']
            )
                ->middleware('admin.role:administrator')
                ->whereNumber('id')
                ->defaults('kind', $kind)
                ->name($path . '.destroy');
        }
    });