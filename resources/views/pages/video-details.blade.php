@extends('layouts.app')

@php
    $artistName = $video->artist_name ?: 'TrendyBeatz';
    $trackTitle = trim((string) $video->TrackTitle);
    $featuring = trim((string) ($video->Featuring ?? ''));

    $displayTitle = $artistName . ' - ' . $trackTitle;
    $keywordTitle = $artistName . ' ' . $trackTitle;

    if (
        $featuring !== ''
        && !preg_match('/\b(?:ft|feat|featuring)\.?\s/i', $trackTitle)
    ) {
        $displayTitle .= ' ft. ' . $featuring;
        $keywordTitle .= ' feat. ' . $featuring;
    }

    $pageTitle = $displayTitle . ' | Watch Video » TrendyBeatz';

    $metaKeywords = implode(', ', [
        $keywordTitle . ' video',
        'Download ' . $keywordTitle . ' video',
        'Watch ' . $trackTitle . ' video',
        'Stream ' . $keywordTitle . ' video',
        'download video mp4 ' . $keywordTitle,
        'Download ' . $displayTitle . ' MP4',
        $keywordTitle . ' music video',
    ]);

    $description = collect([
        $video->introduction ?? '',
        $video->TrackInfo ?? '',
    ])
        ->map(fn ($text) => trim(strip_tags((string) $text)))
        ->filter(fn ($text) => $text !== '')
        ->implode(' ');

    if ($description === '') {
        $description = 'Discover and Watch ' . $displayTitle
            . ' music video on TrendyBeatz.';
    }

    $description = \Illuminate\Support\Str::limit(
        preg_replace('/\s+/u', ' ', $description),
        280,
        ''
    );

    $canonicalUrl = \App\Support\VideoUrl::detail($video);

    $coverPath = trim((string) ($video->CoverUrl ?? ''));

    if ($coverPath === '') {
        $socialImage = '';
    } elseif (preg_match('~^https?://~i', $coverPath)) {
        $socialImage = $coverPath;
    } else {
        $coverPath = ltrim($coverPath, '/');

        $socialImage = asset(
            str_starts_with($coverPath, 'images/')
                ? $coverPath
                : 'images/' . $coverPath
        );
    }
@endphp

@section('title', $pageTitle)
@section('meta_description', $description)
@section('meta_keywords', $metaKeywords)
@section('canonical', $canonicalUrl)
@section('social_title', $displayTitle)
@section('social_description', $description)

@if ($socialImage !== '')
    @section('social_image', $socialImage)
@endif


@section('content')
    @php

        $artistSlug = \Illuminate\Support\Str::slug($artistName);
        
        $videoFile = trim((string) $video->TrackUrl);

        if ($videoFile === '') {
            $videoUrl = null;
        } elseif (preg_match('~^https?://~i', $videoFile)) {
            $videoUrl = $videoFile;
        } else {
            $videoUrl = asset(ltrim($videoFile, '/'));
        }

        /*
         * The old scriptUrl can contain HTML or JavaScript.
         * Extract a YouTube URL only; do not render scriptUrl as HTML.
         */
        $youtubeEmbed = null;

        foreach ([
            $video->youtube_embed_url ?? '',
            $video->scriptUrl ?? '',
        ] as $youtubeValue) {
            $youtubeValue = html_entity_decode(
                (string) $youtubeValue,
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            );
        
            if (preg_match(
                '~(?:youtube(?:-nocookie)?\.com/(?:watch\?(?:[^"\s<>]*&)?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})(?![A-Za-z0-9_-])~i',
                $youtubeValue,
                $youtubeMatch
            )) {
                $youtubeEmbed = 'https://www.youtube-nocookie.com/embed/'
                    . $youtubeMatch[1];
        
                break;
            }
        }
        
        
        $digitalStoreUrl = trim((string) ($video->buy_song ?? ''));

        if (
            !filter_var($digitalStoreUrl, FILTER_VALIDATE_URL)
            || !in_array(
                strtolower((string) parse_url($digitalStoreUrl, PHP_URL_SCHEME)),
                ['http', 'https'],
                true
            )
        ) {
            $digitalStoreUrl = null;
        }

        $videoLink = function ($item) use ($video): string {
            $itemSlug = trim((string) $item->slug, " \t\n\r\0\x0B/");

            if ($itemSlug === '') {
                $itemSlug = \Illuminate\Support\Str::slug(
                    $video->artist_name . ' '
                    . $item->track_title . ' '
                    . ($item->featuring ? 'ft ' . $item->featuring : '')
                );
            }

            return route('video_details', [$item->id, $itemSlug]);
        };
    @endphp

    {{-- Main video panel --}}
    <article class="tb-music-detail tb-music-detail-primary tb-video-detail">
        <header class="tb-music-detail-header">
            <h1>{{ $displayTitle }} Video</h1>

            <div class="tb-music-detail-cover">
                <span class="tb-music-detail-placeholder" aria-hidden="true">
                    TB
                </span>

                @if ($socialImage !== '')
                    <img
                        src="{{ $socialImage }}"
                        alt="{{ $displayTitle }} video cover"
                        onerror="this.remove()"
                    >
                @endif
            </div>

            <p class="tb-music-detail-posted">
                @if ($video->posted_by_name)
                    Posted By:
                    <a
                        class="tb-music-detail-poster-link"
                        href="{{ route(
                            'videos.posted_by',
                            \Illuminate\Support\Str::slug($video->posted_by_name)
                        ) }}"
                    >
                        <strong>{{ $video->posted_by_name }}</strong>
                    </a>

                    <span aria-hidden="true">•</span>
                @endif

                <time datetime="{{ now()->toDateString() }}">
                    {{ now()->format('M d, Y') }}
                </time>
            </p>
        </header>

        <section class="tb-music-detail-section">
            <h2 class="tb-music-detail-heading">
                Video Details
            </h2>

            <div class="tb-music-detail-facts">
                <p>
                    <strong>Artist Name:</strong>
                    <a
                        class="tb-music-detail-blue"
                        href="{{ route('artists.show', $artistSlug) }}"
                        style="text-decoration: none;"
                    >
                        {{ $artistName }}
                    </a>
                </p>

                @if ($featuredArtists->isNotEmpty())
                    <p>
                        <strong>Featuring:</strong>

                        @foreach ($featuredArtists as $featuredArtist)
                            @if (!$loop->first)
                                <span aria-hidden="true">, </span>
                            @endif

                            @if ($featuredArtist['slug'])
                                <a
                                    class="tb-music-detail-red"
                                    href="{{ route('artists.show', $featuredArtist['slug']) }}"
                                    style="text-decoration: none;"
                                >
                                    {{ $featuredArtist['name'] }}
                                </a>
                            @else
                                <span class="tb-music-detail-red">
                                    {{ $featuredArtist['name'] }}
                                </span>
                            @endif
                        @endforeach
                    </p>
                @endif

                <p class="tb-music-detail-green">
                    <strong>Track Title:</strong>
                    {{ $trackTitle }}
                </p>

                @if (filled($video->YearOfRelease))
                    <p>
                        <strong>Year of Release:</strong>
                        <a
                            class="tb-music-detail-red"
                            href="{{ route('videos.year', $video->YearOfRelease) }}"
                            style="text-decoration: none;"
                        >
                            {{ $video->YearOfRelease }} Videos
                        </a>
                    </p>
                @endif

                @if (filled($video->directedby))
                    <p>
                        <strong>Video Directed By:</strong>
                        {{ $video->directedby }}
                    </p>
                @endif

                <p>
                    <strong>Category:</strong>
                    <a
                      
                        href="{{ route('videos.index') }}"
                        style="text-decoration: none;"
                    >
                        Latest Video
                    </a>
                </p>

                @if (in_array($video->country_id, ['naija', 'ghana', 'african'], true))
                    <p>
                        <strong>Country:</strong>
                        <a
                            class="tb-music-detail-blue"
                            href="{{ route('videos.' . $video->country_id) }}"
                            style="text-decoration: none;"
                        >
                            {{ ucfirst($video->country_id) }} Music Video
                        </a>
                    </p>
                @endif


                @php
                    $copyrightFilename = basename(
                        trim((string) ($video->copyright_image ?? ''))
                    );

                    $hasCopyrightPermission = $copyrightFilename !== '';

                    $copyrightLabel = $hasCopyrightPermission
                        ? 'Direct Copyright Permission'
                        : 'Fair Use';

                    $copyrightImageUrl = $hasCopyrightPermission
                        ? asset('images/copyright/' . $copyrightFilename)
                        : null;
                @endphp

                <p class="tb-copyright-row">
                    <strong>Copyright License:</strong>

                    <button
                        type="button"
                        class="tb-copyright-link"
                        data-copyright-open
                        aria-haspopup="dialog"
                        aria-controls="tb-copyright-dialog"
                    >
                        {{ $copyrightLabel }}
                    </button>
                </p>

                <dialog
                    id="tb-copyright-dialog"
                    class="tb-copyright-dialog{{ $hasCopyrightPermission ? '' : ' tb-copyright-dialog--fair-use' }}"
                    aria-labelledby="tb-copyright-title"
                >
                    <div class="tb-copyright-header">
                        <h2 id="tb-copyright-title">
                            {{ $copyrightLabel }}
                        </h2>

                        <button
                            type="button"
                            class="tb-copyright-close"
                            data-copyright-close
                            aria-label="Close copyright notice"
                            autofocus
                        >
                            &times;
                        </button>
                    </div>

                    <div class="tb-copyright-body">
                        @if ($hasCopyrightPermission)
                            <img
                                class="tb-copyright-image"
                                src="{{ $copyrightImageUrl }}"
                                alt="{{ $copyrightLabel }} notice for {{ $displayTitle }}"
                            >

                            <p class="tb-copyright-image-error" hidden>
                                The notice image is currently unavailable.
                            </p>
                        @else
                            <div class="tb-copyright-fair-use">
                                <p>
                                    <strong>Fair Use Notice</strong>
                                </p>

                                <p>
                                    This notice relates to the music video for
                                    <strong class="tb-copyright-full-title">{{ $displayTitle }}</strong>.
                                    All copyrights in this video, its sound recording
                                    and associated artwork belong to their respective
                                    copyright holders.
                                </p>

                                <p>
                                    TrendyBeatz presents this page for music discovery,
                                    information and commentary. No ownership of the
                                    video or its associated materials is claimed.
                                </p>                           

                            

                            </div>

                            <div class="tb-copyright-dmca">
                                Do you want a DMCA removal? Send a removal email to
                                <a href="mailto:DMCA@Trendybeatz.com">
                                    DMCA@Trendybeatz.com
                                </a>.
                            </div>
                        @endif
                    </div>
                </dialog>
            </div>
        </section>

        @if (
            filled($video->introduction)
            || filled($video->TrackInfo)
            || filled($video->trackinfo1)
            || filled($video->trackinfo2)
        )
            <section class="tb-music-detail-section">
                <h2 class="tb-music-detail-heading">
                    About This Video
                </h2>

                <div class="tb-music-detail-description">
                    @if (filled($video->introduction))
                        <p class="tb-music-detail-introduction">
                            {{ strip_tags($video->introduction) }}
                        </p>
                    @endif

                    @foreach ([$video->TrackInfo, $video->trackinfo1, $video->trackinfo2] as $paragraph)
                        @if (filled($paragraph))
                            @php
                                $text = preg_replace(
                                    '/<br\s*\/?>/i',
                                    "\n",
                                    (string) $paragraph
                                );
                    
                                $text = trim(strip_tags($text));
                            @endphp
                    
                            <p>{!! nl2br(e($text)) !!}</p>
                        @endif
                    @endforeach
                    
                    <!--<p class="tb-artist-discovery-note">
                        <em>
                            Discover Latest {{ $artistName }} Videos, Songs/Music &amp; Albums/EP's right here On TrendyBeatz.
                        </em>
                    </p>-->
                </div>
            </section>
        @endif
        
        <!--<div class="tb-green-button-group">
        <a
            class="tb-green-button"
            href="https://t.me/+TodwxcYXdmu-P5Mh"
            target="_blank"
            rel="nofollow"
        >
            Join Our Telegram Group For Latest Music
        </a>
    
        <a
            class="tb-green-button"
            href="https://trendybeatz.com/blog/why-you-should-stop-downloading-music-and-start-streaming-instead"
            target="_blank"
        >
            Read: Why You Should Stop Downloading Music and Start Streaming Instead
        </a>
    </div>-->

      <!--  @if ($youtubeEmbed)
            <section class="tb-music-detail-listening">
                <h2>Legally Watch and Stream {{ $displayTitle }} on TrendyBeatz Below:</h2>
        
                <div class="tb-music-detail-youtube">
                    <iframe
                        src="{{ $youtubeEmbed }}"
                        title="{{ $displayTitle }} video"
                        loading="lazy"
                        allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                    ></iframe>
                </div>
            </section>
        @endif
        
        
        {{-- Digital store --}}
            @if ($digitalStoreUrl)
                <div class="tb-music-detail-digital">
                    <a
                        class="tb-music-detail-store"
                        href="{{ $digitalStoreUrl }}"
                        target="_blank"
                        rel="nofollow"
                    >
                        Stream {{ $displayTitle }} on Digital Store
                    </a>
                </div>
            @endif
        
        
        
        
        @if ($videoUrl)
            <section class="tb-music-detail-listening">
                <h2>Watch {{ $displayTitle }} Legally on TrendyBeatz Below:</h2>
        
                <video
                    class="tb-video-detail-player"
                    controls
                    preload="metadata"
                    @if ($socialImage !== '') poster="{{ $socialImage }}" @endif
                >
                    <source src="{{ $videoUrl }}">
                    Your browser does not support video playback.
                </video>
        
                <a
                    class="tb-music-detail-download"
                    href="{{ $videoUrl }}"
                >
                    Download {{ $displayTitle }} Video mp4 Legally
                </a>
            </section>
        @endif-->
    </article>

    {{-- The share section  --}}

    <section class="tb-share-card">
        <p class="tb-share-heading">
            🔗 Share <b>{{ $displayTitle }}</b> with others on
        </p>

        <div class="tb-share-row">
            <button
                type="button"
                class="tb-share-copy"
                onclick="tbCopyPageLink(this)"
                aria-live="polite"
            >Copy Link</button>
            <div class="a2a_kit a2a_kit_size_24 a2a_default_style">
                <a class="a2a_button_facebook"></a>
                <a class="a2a_button_x"></a>
                <a class="a2a_button_email"></a>
                <a class="a2a_button_pinterest"></a>
                <a class="a2a_button_telegram"></a>
                <a class="a2a_button_whatsapp"></a>
            </div>

            
        </div>
    </section>

    
    <div class="tb-music-detail tb-music-detail-related tb-video-detail">
        @if ($otherVideos->isNotEmpty())
            <section class="tb-music-detail-discovery">
                <h2 class="tb-music-detail-heading">
                    Discover &amp; Watch {{ $artistName }} Other Videos
                </h2>

                <div class="tb-music-detail-discovery-list">
                    @foreach ($otherVideos as $item)
                        <a href="{{ \App\Support\VideoUrl::detail($item) }}">
                            <span class="tb-music-detail-icon" aria-hidden="true">
                                ▶
                            </span>

                            <span class="tb-music-detail-discovery-text">
                                <strong>
                                    {{ $artistName }} - {{ $item->track_title }}
                                </strong>

                                <span>
                                    

                                   @if (filled($item->featuring))
                                       <b> feat. {{ $item->featuring }}</b>
                                    @endif
                              
                                </span>

                                <small>
                                    Tap to watch                                    
                                </small>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($artistSongs->isNotEmpty())
            <section class="tb-music-detail-discovery">
                <h2 class="tb-music-detail-heading">
                    Discover &amp; Stream {{ $artistName }} Other Songs
                </h2>

                <div class="tb-music-detail-discovery-list">
                    @foreach ($artistSongs as $song)
                        <a href="{{ \App\Support\MusicUrl::detail($song) }}">
                            <span class="tb-music-detail-icon" aria-hidden="true">
                                ♫
                            </span>

                            <span class="tb-music-detail-discovery-text">
                                <strong>
                                    {{ $artistName }} - {{ $song->TrackTitle }}
                                </strong>

                                @if (filled($song->Featuring))
                                    <span>
                                      <b>  feat. {{ $song->Featuring }}</b>
                                    </span>
                                @endif

                                <small>Tap to Stream</small>
                            </span>
                        </a>
                    @endforeach
                </div>

                <a
                    class="tb-home-view-all"
                    href="{{ route('artists.show', $artistSlug) }}"
                >
                    View All {{ $artistName }} Songs →
                </a>
            </section>
        @endif

                

        <section class="tb-music-detail-discovery">
            <h2 class="tb-music-detail-heading">
                Discover Latest Music &amp; Videos
            </h2>

            <div class="tb-music-detail-discovery-list">
                @foreach ($latestSongs as $song)
                    <a href="{{ \App\Support\MusicUrl::detail($song) }}">
                        <span class="tb-music-detail-icon" aria-hidden="true">
                            ♫
                        </span>

                        <span class="tb-music-detail-discovery-text">
                            <strong>
                                {{ $song->artist_name }}
                                - {{ $song->TrackTitle }}
                            </strong>

                            <span>
                                    @if (filled($song->Featuring))
                                      <b>  feat. {{ $song->Featuring }}</b>
                                    @endif
                            </span>

                            <small>Tap to Stream</small>
                        </span>
                    </a>
                @endforeach

                <a class="tb-home-view-all" href="{{route('music.download')}}">
                    View All Latest Music→
                </a>

                @foreach ($latestVideos as $item)
                    <a href="{{ \App\Support\VideoUrl::detail($item) }}">
                        <span class="tb-music-detail-icon" aria-hidden="true">
                            ▶
                        </span>

                        <span class="tb-music-detail-discovery-text">
                            <strong>{{ $item->artist_name }} - {{ $item->track_title }}</strong>
                              
                            
                            <span>
                                @if (filled($item->Featuring))
                                      <b>  feat. {{ $item->Featuring }}</b>
                                    @endif
                            </span>
                            <small>Tap to Watch</small>
                        </span>
                    </a>
                @endforeach
            </div>

            <a class="tb-home-view-all" href="{{route('videos.index')}}">
                    View All Latest Videos →
                </a>
        </section>
    </div>

    

    @include('partials.comments', [
        'postType' => 'video',
        'postId' => $video->id,
        'postTitle' => $artistName . ' - '
            . ($video->track_title ?? $video->TrackTitle),
    ])
@endsection