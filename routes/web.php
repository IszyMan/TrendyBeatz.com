<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;

Route::get('/', [HomeController::class, 'index'])
    ->name('home');


Route::get('/song-of-the-day', [PageController::class, 'songOfTheDay'])->name('songs.day');     

Route::get('/music-download', [PageController::class, 'allMusic'])
    ->name('music.download');

Route::get('/musics', [PageController::class, 'allMusic'])
    ->name('music.all');

Route::get('/musics/naija', [PageController::class, 'naija'])
    ->name('music.naija');

Route::get('/musics/ghana', [PageController::class, 'ghana'])
    ->name('music.ghana');

Route::get('/musics/african', [PageController::class, 'african'])
    ->name('music.african');

Route::get('/musics/gospel', [PageController::class, 'gospel'])
    ->name('music.gospel');

Route::get('/musics/highlife', [PageController::class, 'highlife'])
    ->name('music.highlife');

Route::get('/artist-albums', [PageController::class, 'albums'])
    ->name('albums.archived');

Route::get('/latest-videos', [PageController::class, 'videos'])
    ->name('videos.archived');

Route::get('/djmix', [PageController::class, 'mixes'])
    ->name('mixes.archived');

Route::get('/artists', [PageController::class, 'artists'])
    ->name('artists.index');

Route::get('/blogs/{category}', [PageController::class, 'blogCategory'])
    ->whereIn('category', [
        'celebrity-news',
        'hot-gists',
        'music-reviews',
    ])
    ->name('blogs.category');

Route::get('/search', [PageController::class, 'search'])
    ->name('search');

Route::get('/music/{category}', [PageController::class, 'music'])
    ->whereIn('category', [
        'naija',
        'ghana',
        'african',
        'gospel',
        'highlife',
    ])
    ->name('music.index');

Route::get('/songs/{id}/{slug}', [PageController::class, 'song'])
    ->whereNumber('id')
    ->name('songs.show');

  

Route::get('/videos', [PageController::class, 'videos'])
    ->name('videos.index');

Route::get('/albums', [PageController::class, 'albums'])
    ->name('albums.index');

Route::get('/albums/{id}/{slug}', [PageController::class, 'album'])
    ->whereNumber('id')
    ->name('albums.show');

Route::get('/dj-mixes', [PageController::class, 'mixes'])
    ->name('mixes.index');

Route::get('/dj-mixes/{id}/{slug}', [PageController::class, 'mix'])
    ->whereNumber('id')
    ->name('mixes.show');

Route::get('/blogs', [PageController::class, 'blogs'])
    ->name('blogs.index');

Route::get('/blogs/{id}/{slug}', [PageController::class, 'blog'])
    ->whereNumber('id')
    ->name('blogs.show');