@extends('layouts.app')

@php
    $pageTitle = 'Disclaimer — TrendyBeatz';

    $metaDescription = 'Read the TrendyBeatz disclaimer covering website information, music and video content, copyright concerns and limitations of liability.';

    $metaKeywords = 'TrendyBeatz disclaimer, content disclaimer, copyright policy, music downloads, website liability';

    $canonical = route('page.disclaimer');
@endphp

@section('title', $pageTitle)
@section('meta_description', $metaDescription)
@section('meta_keywords', $metaKeywords)
@section('canonical', $canonical)
@section('social_title', $pageTitle)
@section('social_description', $metaDescription)

@section('content')
    <style>
        .tb-disclaimer {
            --disclaimer-green: #198754;
            width: 100%;
            max-width: 900px;
            margin: 28px auto;
            padding: 0 16px;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #25332b;
            overflow-wrap: anywhere;
        }

        .tb-disclaimer * {
            box-sizing: border-box;
        }

        .tb-disclaimer-header {
            padding: 26px;
            border: 1px solid #dce8df;
            border-top: 4px solid var(--disclaimer-green);
            border-radius: 8px;
            background: #f3f8f5;
        }

        .tb-disclaimer-label {
            margin: 0 0 10px;
            color: var(--disclaimer-green);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        .tb-disclaimer h1 {
            margin: 0 0 12px;
            color: #14251b;
            font-size: clamp(27px, 4vw, 36px);
            font-weight: 800;
            line-height: 1.2;
        }

        .tb-disclaimer-updated {
            margin: 0;
            color: #617168;
            font-size: 13px;
            line-height: 1.6;
        }

        .tb-disclaimer-section {
            margin-top: 18px;
            padding: 22px 24px;
            border: 1px solid #e0e6e2;
            border-radius: 8px;
            background: #fff;
        }

        .tb-disclaimer h2 {
            margin: 0 0 12px;
            padding-left: 12px;
            border-left: 3px solid var(--disclaimer-green);
            color: #14251b;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.4;
        }

        .tb-disclaimer-section p {
            margin: 0 0 16px;
            font-size: 15px;
            line-height: 1.9;
        }

        .tb-disclaimer-section p:last-child {
            margin-bottom: 0;
        }

        .tb-disclaimer a {
            color: #0066cc;
            text-decoration: none;
        }

        .tb-disclaimer a:hover {
            text-decoration: underline;
        }

        .tb-disclaimer a:focus-visible {
            outline: 3px solid var(--disclaimer-green);
            outline-offset: 4px;
        }

        @media (max-width: 600px) {
            .tb-disclaimer {
                margin: 18px auto;
                padding: 0 12px;
            }

            .tb-disclaimer-header {
                padding: 20px 16px;
            }

            .tb-disclaimer-section {
                padding: 18px 16px;
            }

            .tb-disclaimer h2 {
                font-size: 18px;
            }
        }
    </style>

    <article class="tb-disclaimer">
        <header class="tb-disclaimer-header">
            <p class="tb-disclaimer-label">TrendyBeatz Media</p>

            <h1>Disclaimer</h1>

            <p class="tb-disclaimer-updated">
                Last updated {{ date('F Y') }}
            </p>
        </header>

        <section class="tb-disclaimer-section">
            <h2>Information and Accuracy</h2>

            <p>
                The information and content on TrendyBeatz is
                provided for entertainment and informational
                purposes only. While we strive to keep all content
                accurate and up to date, we make no warranties of
                any kind, express or implied, about the completeness,
                accuracy or reliability of any content on this site.
            </p>
        </section>

        <section class="tb-disclaimer-section">
            <h2>Content and Copyright</h2>

            <p>
                All music, audio and video content available for
                download on TrendyBeatz is either submitted directly
                by artists and rights holders, or linked to
                third-party sources. We do not host copyrighted
                content without permission.
            </p>

            <p>
                If you believe your rights have been infringed,
                please see our
                <a href="{{ route('page.dmca') }}">DMCA policy</a>.
            </p>
        </section>

        <section class="tb-disclaimer-section">
            <h2>Limitation of Liability</h2>

            <p>
                TrendyBeatz shall not be liable for any losses or
                damages arising from your use of, or inability to
                use, this website.
            </p>
        </section>
    </article>
@endsection