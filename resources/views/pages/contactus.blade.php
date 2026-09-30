@extends('layouts.app')

@php
    $pageTitle = 'Contact Us — TrendyBeatz';

    $metaDescription = 'Contact TrendyBeatz for music submissions, promotion, advertising and partnership enquiries. Reach our team by email or business WhatsApp.';

    $metaKeywords = 'contact TrendyBeatz, TrendyBeatz email, TrendyBeatz WhatsApp, music submissions, music promotion, advertising enquiries';

    $canonical = route('page.contact');
@endphp

@section('title', $pageTitle)
@section('meta_description', $metaDescription)
@section('meta_keywords', $metaKeywords)
@section('canonical', $canonical)
@section('social_title', $pageTitle)
@section('social_description', $metaDescription)

@section('content')
    <style>
        .tb-contact {
            --contact-green: #198754;
            width: 100%;
            max-width: 900px;
            margin: 28px auto;
            padding: 0 16px;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #25332b;
            overflow-wrap: anywhere;
        }

        .tb-contact * {
            box-sizing: border-box;
        }

        .tb-contact-header {
            padding: 26px;
            border: 1px solid #dce8df;
            border-top: 4px solid var(--contact-green);
            border-radius: 8px;
            background: #f3f8f5;
        }

        .tb-contact-label {
            margin: 0 0 10px;
            color: var(--contact-green);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        .tb-contact h1 {
            margin: 0 0 12px;
            color: #14251b;
            font-size: clamp(27px, 4vw, 36px);
            font-weight: 800;
            line-height: 1.2;
        }

        .tb-contact-updated {
            margin: 0;
            color: #617168;
            font-size: 13px;
            line-height: 1.6;
        }

        .tb-contact-intro {
            margin: 24px 0;
            font-size: 16px;
            line-height: 1.85;
        }

        .tb-contact-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .tb-contact-card {
            padding: 24px;
            border: 1px solid #e0e6e2;
            border-radius: 8px;
            background: #fff;
        }

        .tb-contact h2 {
            margin: 0 0 12px;
            padding-left: 12px;
            border-left: 3px solid var(--contact-green);
            color: #14251b;
            font-size: 20px;
            line-height: 1.4;
        }

        .tb-contact-card p {
            margin: 0 0 16px;
            font-size: 15px;
            line-height: 1.8;
        }

        .tb-contact-card p:last-child {
            margin-bottom: 0;
        }

        .tb-contact a {
            color: #0066cc;
            text-decoration: none;
        }

        .tb-contact a:hover {
            text-decoration: underline;
        }

        .tb-contact a:focus-visible {
            outline: 3px solid var(--contact-green);
            outline-offset: 4px;
        }

        .tb-contact .tb-contact-button {
            display: inline-block;
            max-width: 100%;
            padding: 11px 18px;
            border-radius: 5px;
            background: var(--contact-green);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.5;
            text-align: center;
            text-decoration: none;
        }

        .tb-contact .tb-contact-button:hover {
            background: #146c43;
            text-decoration: none;
        }

        .tb-contact-office {
            grid-column: 1 / -1;
            background: #f3f8f5;
            border-color: #dce8df;
        }

        .tb-contact-office address {
            font-size: 15px;
            font-style: normal;
            line-height: 1.8;
        }

        .tb-contact-note {
            margin-top: 22px;
            padding: 20px 24px;
            border-left: 4px solid var(--contact-green);
            background: #f3f8f5;
            border-radius: 4px;
        }

        .tb-contact-note p {
            margin: 0;
            font-size: 14px;
            line-height: 1.85;
        }

        @media (max-width: 600px) {
            .tb-contact {
                margin: 18px auto;
                padding: 0 12px;
            }

            .tb-contact-header {
                padding: 20px 16px;
            }

            .tb-contact-grid {
                grid-template-columns: 1fr;
            }

            .tb-contact-card,
            .tb-contact-note {
                padding: 18px 16px;
            }

            .tb-contact h2 {
                font-size: 18px;
            }

            .tb-contact .tb-contact-button {
                width: 100%;
            }
        }
    </style>

    <article class="tb-contact">
        <header class="tb-contact-header">
            <p class="tb-contact-label">TrendyBeatz Media</p>

            <h1>Contact TrendyBeatz</h1>

            <p class="tb-contact-updated">
                Last updated {{ date('F Y') }}
            </p>
        </header>

        <p class="tb-contact-intro">
            Have a question, a music submission, or a business inquiry?
            We'd love to hear from you.
        </p>

        <div class="tb-contact-grid">
            <section class="tb-contact-card">
                <h2>Email Us</h2>

                <p>
                    <a href="mailto:info@trendybeatz.com">
                        info@trendybeatz.com
                    </a>
                </p>

                <a
                    class="tb-contact-button"
                    href="mailto:info@trendybeatz.com"
                >
                    Send an Email
                </a>
            </section>

            <section class="tb-contact-card">
                <h2>Business WhatsApp</h2>

                <p>
                    <a
                        href="https://wa.me/2349076131844"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        +234 907 613 1844
                    </a>
                </p>

                <a
                    class="tb-contact-button"
                    href="https://wa.me/2349076131844"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Chat on WhatsApp
                </a>
            </section>

            <section class="tb-contact-card tb-contact-office">
                <h2>Our Office</h2>

                <address>
                    <strong>TrendyBeatz Media</strong><br>
                    Lekki, Lagos, Nigeria.
                </address>
            </section>
        </div>

        <div class="tb-contact-note">
            <p>
                For music promotion, advertising or partnership
                enquiries, please include your name, brand or artist
                name and a brief description of your request in
                your message. We respond within a few minutes.
            </p>
        </div>
    </article>
@endsection