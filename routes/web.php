<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;

Route::get('/', [HomeController::class, 'index'])
    ->name('home');


Route::get('/song-of-the-day', [PageController::class, 'songOfTheDay'])->name('songs.day');   


Route::get('/legal-download/{id}/{slug}', [PageController::class, 'musicDetails'])->whereNumber('id')
    ->name('music_details');

Route::get('/songs-posted-by/{slug}', [PageController::class, 'songsPostedBy'])->name('songs.posted_by');    

Route::get('/music-download', [PageController::class, 'musicDownload'])->name('music.download');

Route::get('/musics/gospel',[PageController::class, 'gospelSongs'])->name('music.gospel');

Route::get('/musics/highlife',[PageController::class, 'highlifeSongs'])->name('music.highlife');

Route::get('/musics', [PageController::class, 'allMusic'])
    ->name('music.all');

Route::get('/musics/naija', [PageController::class, 'countrySongs'])->defaults('country', 'naija')
    ->name('music.naija');

Route::get('/musics/ghana', [PageController::class, 'countrySongs'])->defaults('country', 'ghana')
    ->name('music.ghana');

Route::get('/musics/african', [PageController::class, 'countrySongs'])->defaults('country', 'african')
    ->name('music.african');

Route::get('/download-latest-videos',[PageController::class, 'latestVideos'])->name('videos.index');


Route::get('/download-video/{id}/{slug}', [PageController::class, 'videoDetails'])->whereNumber('id')
    ->name('video_details');

Route::get('/music-videos-posted-by/{slug}',[PageController::class, 'videosPostedBy'])->name('videos.posted_by');    

Route::get('/artists', [PageController::class, 'artists'])->name('artists.index');

Route::get('/artists/sections/{country}', [PageController::class, 'artistCountrySection'])
    ->whereIn('country', ['naija', 'ghana', 'african'])->name('artists.section');

Route::get('/artists/{slug}', [PageController::class, 'artistDetails'])->name('artists.show');

Route::get('/artist-albums', [PageController::class, 'albums'])->name('albums.index');

Route::get('/artist-albums/{id}/{slug}', [PageController::class, 'albumDetails'])
    ->whereNumber('id')->name('albums.show');

Route::get('/album-posted-by/{slug}',[PageController::class, 'albumPostedBy'])->name('albums.posted_by');  

Route::get('/musics', [PageController::class, 'allMusic'])->name('music.all');


Route::get('/djmix', [PageController::class, 'djMixes'])->name('mixes.index');

Route::get('/djmix/posted-by/{slug}', [PageController::class, 'mixesPostedBy'])
    ->name('mixes.posted_by');

Route::get('/djmix/{id}/{slug}', [PageController::class, 'mixDetails'])
    ->whereNumber('id')->name('mixes.show');

Route::get('/blogs', [PageController::class, 'blogs'])
    ->name('blogs.index');

Route::get('/blogs/{category}', [PageController::class, 'blogCategory'])
    ->whereIn('category', [
        'music-reviews',
        'hot-gists',
        'celebrity-news',
        'education',
        'articles',
        'networth',
        'news',
        'sport-news',
    ])
    ->name('blogs.category');

Route::get('/blog/{slug}', [PageController::class, 'blogDetails'])
    ->name('blogs.show');

Route::get('/search', [PageController::class, 'search'])
    ->name('search');

