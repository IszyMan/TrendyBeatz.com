@extends('layouts.app')

@php
    $pageTitle = 'Terms & Conditions — TrendyBeatz';

    $metaDescription = 'Read the TrendyBeatz terms and conditions covering content usage, user conduct, updates to our terms and contact details.';

    $metaKeywords = 'TrendyBeatz terms and conditions, TrendyBeatz terms of use, content usage, user conduct, music download terms';

    $canonical = route('page.terms');
@endphp

@section('title', $pageTitle)
@section('meta_description', $metaDescription)
@section('meta_keywords', $metaKeywords)
@section('canonical', $canonical)
@section('social_title', $pageTitle)
@section('social_description', $metaDescription)

@section('content')
    <style>
        .tb-terms {
            --terms-green: #198754;
            --terms-text: #25332b;
            width: 100%;
            max-width: 900px;
            margin: 28px auto;
            padding: 0 16px;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: var(--terms-text);
            overflow-wrap: anywhere;
        }

        .tb-terms * {
            box-sizing: border-box;
        }

        .tb-terms-header {
            padding: 26px;
            border: 1px solid #dce8df;
            border-top: 4px solid var(--terms-green);
            border-radius: 8px;
            background: #f3f8f5;
        }

        .tb-terms-label {
            margin: 0 0 10px;
            color: var(--terms-green);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        .tb-terms h1 {
            margin: 0 0 12px;
            color: #14251b;
            font-size: clamp(27px, 4vw, 36px);
            font-weight: 800;
            line-height: 1.2;
        }

        .tb-terms-updated {
            margin: 0;
            color: #617168;
            font-size: 13px;
            line-height: 1.6;
        }

        .tb-terms-intro {
            margin: 24px 0;
            font-size: 16px;
            line-height: 1.85;
        }

        .tb-terms-section {
            margin: 0 0 18px;
            padding: 22px 24px;
            border: 1px solid #e0e6e2;
            border-radius: 8px;
            background: #fff;
        }

        .tb-terms h2 {
            margin: 0 0 12px;
            padding-left: 12px;
            border-left: 3px solid var(--terms-green);
            color: #14251b;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.4;
        }

        .tb-terms-section p {
            margin: 0;
            font-size: 15px;
            line-height: 1.9;
        }

        .tb-terms-contact {
            background: #f3f8f5;
            border-color: #dce8df;
        }

        .tb-terms a {
            color: #0066cc;
            text-decoration: none;
        }

        .tb-terms a:hover {
            text-decoration: underline;
        }

        .tb-terms a:focus-visible {
            outline: 3px solid var(--terms-green);
            outline-offset: 4px;
        }

        @media (max-width: 600px) {
            .tb-terms {
                margin: 18px auto;
                padding: 0 12px;
            }

            .tb-terms-header {
                padding: 20px 16px;
            }

            .tb-terms-section {
                padding: 18px 16px;
            }

            .tb-terms h2 {
                font-size: 18px;
            }
        }
    </style>

    <article class="tb-terms">
        <header class="tb-terms-header">
            <p class="tb-terms-label">TrendyBeatz Media</p>

            <h1>Terms &amp; Conditions</h1>

            <p class="tb-terms-updated">
                Last updated {{ date('F Y') }}
            </p>
        </header>

        <p class="tb-terms-intro">
            By accessing or using TrendyBeatz, you agree to be bound
            by these Terms and Conditions. If you do not agree,
            please do not use our site.
        </p>

        <section class="tb-terms-section">
            <h2>Use of Content</h2>

            <p>
                Content on TrendyBeatz — including music, articles
                and images — is provided for personal,
                non-commercial use only. You may not redistribute,
                re-upload, sell or commercially exploit any content
                without prior written permission from TrendyBeatz
                or the respective rights holder.
            </p>
        </section>

        <section class="tb-terms-section">
            <h2>User Conduct</h2>

            <p>
                You agree not to misuse the site, upload harmful
                content, or attempt to gain unauthorised access
                to any part of our platform.
            </p>
        </section>

        <section class="tb-terms-section">
            <h2>Changes to Terms</h2>

            <p>
                We reserve the right to update these terms at any
                time. Continued use of the site after changes
                constitutes acceptance of the updated terms.
            </p>
        </section>

        <section class="tb-terms-section tb-terms-contact">
            <h2>Contact</h2>

            <p>
                Questions? Email
                <a href="mailto:info@trendybeatz.com">
                    info@trendybeatz.com
                </a>.
            </p>
        </section>
    </article>
@endsection