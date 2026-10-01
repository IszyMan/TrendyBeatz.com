<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\StaticPageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CommentController;

Route::get('/', [HomeController::class, 'index'])
    ->name('home');


Route::get('/song-of-the-day', [PageController::class, 'songOfTheDay'])->name('songs.day');  

Route::get('/songs-of-the-week', [PageController::class, 'songsOfTheWeek'])->name('songs.week');

Route::get('/top-trending-songs',[PageController::class, 'topTrendingSongs'])->name('songs.trending');

Route::get('/top-rated-songs',[PageController::class, 'topRatedSongs'])->name('songs.top_rated');

Route::get('/legal-download/{year}/songs',[PageController::class, 'songsByYear'])
    ->where('year', '(?:19|20)[0-9]{2}')->name('music.year');


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

Route::get('/naija-music-videos', [PageController::class, 'countryVideos'])
    ->defaults('country', 'naija')
    ->name('videos.naija');

Route::get('/ghana-music-videos', [PageController::class, 'countryVideos'])
    ->defaults('country', 'ghana')
    ->name('videos.ghana');

Route::get('/african-music-videos', [PageController::class, 'countryVideos'])
    ->defaults('country', 'african')
    ->name('videos.african');    

Route::get('/download-latest-videos',[PageController::class, 'latestVideos'])->name('videos.index');

Route::get('/{year}/videos', [PageController::class, 'videosByYear'])->where('year', '[0-9]{4}')
    ->name('videos.year');


Route::get('/download-video/{id}/{slug}', [PageController::class, 'videoDetails'])->whereNumber('id')
    ->name('video_details');

Route::get('/music-videos-posted-by/{slug}',[PageController::class, 'videosPostedBy'])->name('videos.posted_by');    

Route::get('/artists', [PageController::class, 'artists'])->name('artists.index');

Route::get('/artists/sections/{country}', [PageController::class, 'artistCountrySection'])
    ->whereIn('country', ['naija', 'ghana', 'african'])->name('artists.section');

Route::get('/artists/{slug}', [PageController::class, 'artistDetails'])->name('artists.show');

Route::get('/{year}/albums', [PageController::class, 'albumsByYear'])->where('year', '[0-9]{4}')
    ->name('albums.year');

Route::get('/artist-albums', [PageController::class, 'albums'])->name('albums.index');

Route::get('/artist-albums/{id}/{slug}', [PageController::class, 'albumDetails'])
    ->whereNumber('id')->name('albums.show');

Route::get('/popular-albums',[PageController::class, 'popularAlbums'])->name('albums.popular');    

Route::get('/album-posted-by/{slug}',[PageController::class, 'albumPostedBy'])->name('albums.posted_by');  

Route::get('/dj-mix', [PageController::class, 'djs'])->name('djs.index');

Route::get('/{year}/djmix', [PageController::class, 'mixesByYear'])->where('year', '[0-9]{4}')
    ->name('mixes.year');

Route::get('/dj-mix-{slug}', [PageController::class, 'djDetails'])->name('djs.show');

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

Route::get('/published-by/{slug}', [PageController::class, 'blogsPublishedBy'])
    ->name('blogs.published_by');    

Route::get('/search', [SearchController::class, 'index'])
    ->name('search');

Route::post('/comments', [CommentController::class, 'store'])->middleware('throttle:5,1')
    ->name('comments.store');    


Route::controller(StaticPageController::class)->group(function () {
    Route::get('/privacy-policy', 'privacyPolicy')->name('page.privacy');

    Route::get('/aboutus', 'aboutUs')->name('page.about');

    Route::get('/terms-of-use', 'termsOfUse')->name('page.terms');

    Route::get('/contactus', 'contactUs')->name('page.contact');

    Route::get('/advertise_with_us', 'advertiseWithUs')->name('page.advertise');

    Route::get('/promote-music', 'promoteMusic')->name('page.promote');

    Route::get('/disclaimer', 'disclaimer')->name('page.disclaimer');

    Route::get('/dmca', 'dmca')->name('page.dmca');
});



require __DIR__ . '/admin.php';    

