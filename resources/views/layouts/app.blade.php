<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $metaTitle = trim($__env->yieldContent(
            'title',
            ($title ?? 'Discover Latest Naija Music and Entertainment')
                . ' | TrendyBeatz'
        ));

        $metaDescription = trim($__env->yieldContent(
            'meta_description',
            'Stream and discover the latest Naija songs, Ghana and African music, videos, DJ mixes, albums and entertainment news.'
        ));

        $canonicalUrl = trim($__env->yieldContent(
            'canonical',
            url()->current()
        ));

        $socialImage = trim($__env->yieldContent('social_image'));
    @endphp

    <title>{{ $metaTitle }}</title>

    @hasSection('meta_keywords')
        <meta name="keywords" content="@yield('meta_keywords')">
    @endif

    <meta name="description" content="{{ $metaDescription }}">

    

    <meta name="author" content="Israel Wonah">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

    <link rel="canonical" href="{{ $canonicalUrl }}">

    @hasSection('previous_url')
        <link rel="prev" href="@yield('previous_url')">
    @endif

    @hasSection('next_url')
        <link rel="next" href="@yield('next_url')">
    @endif

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="TrendyBeatz">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">

    @if ($socialImage !== '')
        <meta property="og:image" content="{{ $socialImage }}">
    @endif

    <meta
        name="twitter:card"
        content="{{ $socialImage !== '' ? 'summary_large_image' : 'summary' }}"
    >
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">

    @if ($socialImage !== '')
        <meta name="twitter:image" content="{{ $socialImage }}">
    @endif
    <link rel="stylesheet" href="{{ asset('css/trendybeatz.css') }}">
    <link rel="stylesheet" href="{{ asset('css/trendybeatz-nav.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/faviconn.png') }}">
    <link rel="stylesheet" href="{{ asset('css/comments.css') }}">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v=1">





</head>
<body>
    <header class="site-logo-bar"><a class="site-logo" href="{{ route('home') }}">Trendy<span>Beatz</span></a></header>
    @include('partials.navigation')


    <div class="page-layout">
        <main class="page-main">
            @yield('content')
        </main>

        <aside class="page-aside" aria-label="TrendyBeatz sidebar">
            @include('layouts.aside')
        </aside>
    </div>

    @include('layouts.footer')



    
    <script>
        document.querySelectorAll('[data-nav-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                const panel = document.getElementById(button.getAttribute('aria-controls'));
                const open = button.getAttribute('aria-expanded') !== 'true';
                document.querySelectorAll('[data-nav-toggle]').forEach(function (other) {
                    other.setAttribute('aria-expanded', 'false');
                    document.getElementById(other.getAttribute('aria-controls')).hidden = true;
                });
                button.setAttribute('aria-expanded', String(open));
                panel.hidden = !open;
            });
        });
        document.addEventListener('click', function (event) {
            if (event.target.closest('.mobile-dropdown')) return;
            document.querySelectorAll('[data-nav-toggle]').forEach(function (button) {
                button.setAttribute('aria-expanded', 'false');
                document.getElementById(button.getAttribute('aria-controls')).hidden = true;
            });
        });
    </script>

    <script async src="https://static.addtoany.com/menu/page.js"></script>

    <script>
        async function tbCopyPageLink(button) {
            const url = window.location.href;
            let copied = false;

            try {
                await navigator.clipboard.writeText(url);
                copied = true;
            } catch (error) {
                const input = document.createElement('textarea');
                input.value = url;
                input.style.position = 'fixed';
                input.style.opacity = '0';
                document.body.appendChild(input);
                input.select();

                try {
                    copied = document.execCommand('Copy');
                } finally {
                    input.remove();
                    button.focus();
                }
            }

            if (!copied) {
                window.prompt('Copy this link:', url);
                return;
            }

            clearTimeout(button.copyTimer);
            button.textContent = 'Copied!';

            button.copyTimer = setTimeout(() => {
                button.textContent = 'Copy Link' ;
            }, 3000);
        }
    </script>

    <script src="{{ asset('js/comments.js') }}" defer></script>
</body>
</html>
