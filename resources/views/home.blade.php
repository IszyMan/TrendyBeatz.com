@extends('layouts.app')

@section(
    'title',
    'TrendyBeatz - Nigeria No. 1 Entertainment and Music Discovery Website For Legal Downloads'
)

@section(
    'meta_keywords',
    'TrendyBeatz, latest Nigerian music, Naija music, music discovery, legal music downloads, download music mp3, stream music, Ghana music, African music, gospel songs, highlife music, music videos, albums, EP downloads, DJ mixes, mixtapes, music reviews, entertainment news, artiste profiles, music promotion'
)

@section('content')
    <h1 class="section-heading">
        Discover Latest Naija Music and Entertainment
    </h1>

    <p class="home-date">
        <time datetime="{{ now()->toDateString() }}">
            {{ now()->format('M d, Y') }}
        </time>
    </p>

    @include('partials.home.songs', [
        'id' => 'song-of-the-day',
        'heading' => 'Song of the Day',
        'badge' => 'Song of the Day',
        'items' => $day,
        'more' => route('songs.day'),
    ])

    @include('partials.home.songs', [
        'id' => 'naija',
        'heading' => 'Latest Naija Songs',
        'badge' => 'Naija Music',
        'items' => $naija,
        'more' => route('music.naija'),
    ])

    @include('partials.home.blogs', [
        'id' => 'latest-blog-news',
        'heading' => 'Latest Blog & News',
        'items' => $news,
        'more' => route('blogs.index'),
    ])

    @include('partials.home.songs', [
        'id' => 'song-of-the-week',
        'heading' => 'Song of the Week',
        'badge' => 'Song of the Week',
        'items' => $week,
        'more' => route('songs.week'),
    ])

    @include('partials.home.songs', [
        'id' => 'ghana',
        'heading' => 'Latest Ghana Songs',
        'badge' => 'Ghana Music',
        'items' => $ghana,
        'more' => route('music.ghana'),
    ])

    @include('partials.home.songs', [
        'id' => 'african',
        'heading' => 'Latest African Songs',
        'badge' => 'African Music',
        'items' => $african,
        'more' => route('music.african'),
    ])

    @include('partials.home.albums', [
        'id' => 'latest-albums',
        'heading' => 'Latest Albums',
        'items' => $albums,
        'more' => route('albums.index'),
    ])
    

    @include('partials.home.songs', [
        'id' => 'gospel',
        'heading' => 'Latest Gospel Songs',
        'badge' => 'Gospel Music',
        'items' => $gospel,
        'more' => route('music.gospel'),
    ])

    @include('partials.home.songs', [
        'id' => 'highlife',
        'heading' => 'Latest Highlife Songs',
        'badge' => 'Highlife Music',
        'items' => $highlife,
        'more' => route('music.highlife'),
    ])

    

    @include('partials.home.mixes', [
        'id' => 'latest-dj-mix',
        'heading' => 'Latest DJ Mix',
        'badge' => 'DJ Mix',
        'items' => $mixes,
        'more' => route('mixes.index'),
    ])

    

    @include('partials.home.videos', [
        'id' => 'latest-videos',
        'heading' => 'Latest Videos',
        'badge' => 'Video',
        'items' => $videos,
        'more' => route('videos.index'),
    ])

    

    @include('partials.home.blogs', [
        'id' => 'music-reviews',
        'heading' => 'Music Reviews & News',
        'items' => $reviews,
        'more' => route('blogs.index'),
    ])

@endsection