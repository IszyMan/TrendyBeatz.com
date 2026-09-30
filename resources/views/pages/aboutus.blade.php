@extends('layouts.app')

@php
    $pageTitle = 'About Us - TrendyBeatz.com - Naija Music Museum - Download and Stream Online Free, latest Nigerian Music Mp3 Mp4 - Top Celebrity News - Free Music Upload and Promotion for UpComing Artists';

    $metaDescription = 'About TrendyBeatz Music streaming and download platform, trendybeatz owners and CEOs';

    $metaKeywords = 'about Trendybeatz, about trendybeatz music platform, ceos of trendybeatz, owners of trendybeatz';
@endphp

@section('title', $pageTitle)
@section('meta_description', $metaDescription)
@section('meta_keywords', $metaKeywords)
@section('canonical', route('page.about'))
@section('social_title', $pageTitle)
@section('social_description', $metaDescription)

@section('content')
    <style>
        .tb-about-page {
            width: 100%;
            max-width: 920px;
            margin: 24px auto;
            padding: 0 16px;
            box-sizing: border-box;
            color: #222;
        }

        .tb-about-header {
            margin-bottom: 22px;
            padding-bottom: 12px;
            border-bottom: 3px solid #198754;
        }

        .tb-about-header h1 {
            margin: 0;
            font-size: clamp(26px, 4vw, 32px);
            font-weight: 800;
            line-height: 1.3;
        }

        .tb-about-content {
            font-size: 16px;
            line-height: 1.85;
            overflow-wrap: anywhere;
        }

        .tb-about-content p {
            margin: 0 0 18px;
        }

        .tb-about-section {
            margin-top: 28px;
        }

        .tb-about-section h2 {
            margin: 0 0 14px;
            padding-left: 10px;
            border-left: 4px solid #198754;
            font-size: 21px;
            font-weight: 700;
            line-height: 1.4;
        }

        .tb-about-contact {
            margin-top: 28px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: #f7faf8;
        }

        .tb-about-contact h2 {
            margin-top: 0;
        }

        .tb-about-contact p:last-child {
            margin-bottom: 0;
        }

        .tb-about-page a {
            color: #0066cc;
            text-decoration: none;
        }

        .tb-about-page a:hover {
            text-decoration: underline;
        }

        .tb-about-page a:focus-visible {
            outline: 3px solid #198754;
            outline-offset: 4px;
        }

        .tb-about-page .tb-about-button {
            display: inline-block;
            max-width: 100%;
            margin-top: 6px;
            padding: 11px 18px;
            box-sizing: border-box;
            border-radius: 5px;
            background: #198754;
            color: #fff;
            font-weight: 700;
            text-align: center;
            text-decoration: none;
        }

        .tb-about-page .tb-about-button:hover {
            background: #146c43;
            text-decoration: none;
        }

        @media (max-width: 600px) {
            .tb-about-page {
                margin: 18px auto;
                padding: 0 12px;
            }

            .tb-about-section h2 {
                font-size: 19px;
            }

            .tb-about-contact {
                padding: 16px;
            }
        }
    </style>

    <article class="tb-about-page">
        <header class="tb-about-header">
            <h1>About Us</h1>
        </header>

        <div class="tb-about-content">
            <p>
                TrendyBeatz is the #1 Music Download and Streaming
                Platform in Nigeria and Africa, serving every nook
                and cranny of the continent with good music.
                Since our inception in 2019, we have grown to become
                a music powerhouse, helping artists reach a very
                large audience of music lovers and delivering music
                to people who appreciate good music.
            </p>

            <p>
                Our music uploads are for promotional purposes only,
                as we help artists break grounds with their music
                and reach everyone who might appreciate their crafts.
            </p>

            <section class="tb-about-section">
                <h2>Our Mission</h2>

                <p>
                    Our mission is to deliver artists and their
                    crafts to a wide audience of music lovers who
                    appreciate music as food for the soul.
                </p>
            </section>

            <section class="tb-about-section">
                <h2>Our Vision</h2>

                <p>
                    Our vision is to become the Number 1 Music
                    Download Platform, delivering good music to
                    the general populace and covering everything
                    in the music industry.
                </p>
            </section>

            <section class="tb-about-section">
                <h2>Music Across Africa</h2>

                <p>
                    Our penchant for good user experience and user
                    satisfaction has, over the years, made us the
                    favourite platform for music lovers and the
                    go-to platform for everything music in Africa.
                </p>

                <p>
                    TrendyBeatz is an online music store for all your
                    favourite trending songs from Nigeria, Ghana,
                    Tanzania, South Africa and Africa as a whole.
                </p>
            </section>

            <section class="tb-about-section">
                <h2>Artist Profiles</h2>

                <p>
                    In our artist profiles, you can find everything
                    about an artist, including their biographies,
                    complete albums, singles and download links.
                </p>
            </section>

            <section class="tb-about-section tb-about-contact">
                <h2>Contact Us</h2>

                <p>
                    <strong>TrendyBeatz Entertainment</strong><br>
                    Lekki Peninsula, Lekki, Lagos, Nigeria.
                </p>

                <p>
                    <strong>Email us:</strong>
                    <a href="mailto:info@trendybeatz.com">
                        info@trendybeatz.com
                    </a>
                </p>

                <a
                    class="tb-about-button"
                    href="{{ route('page.contact') }}"
                >
                    Leave a Message
                </a>
            </section>
        </div>
    </article>
@endsection