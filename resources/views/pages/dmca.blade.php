@extends('layouts.app')

@php
    $pageTitle = 'DMCA Copyright Removal Policy — TrendyBeatz';

    $metaDescription = 'Contact TrendyBeatz to report copyright infringement and request content removal. Find submission instructions and our copyright contact email.';

    $metaKeywords = 'TrendyBeatz DMCA, copyright removal, copyright infringement, content removal request, TrendyBeatz copyright policy';

    $canonical = route('page.dmca');
@endphp

@section('title', $pageTitle)
@section('meta_description', $metaDescription)
@section('meta_keywords', $metaKeywords)
@section('canonical', $canonical)
@section('social_title', $pageTitle)
@section('social_description', $metaDescription)

@section('content')
    <style>
        .tb-dmca {
            --dmca-green: #198754;
            width: 100%;
            max-width: 900px;
            margin: 28px auto;
            padding: 0 16px;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #25332b;
            overflow-wrap: anywhere;
        }

        .tb-dmca * {
            box-sizing: border-box;
        }

        .tb-dmca-header {
            padding: 26px;
            border: 1px solid #dce8df;
            border-top: 4px solid var(--dmca-green);
            border-radius: 8px;
            background: #f3f8f5;
        }

        .tb-dmca-label {
            margin: 0 0 10px;
            color: var(--dmca-green);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        .tb-dmca h1 {
            margin: 0 0 12px;
            color: #14251b;
            font-size: clamp(27px, 4vw, 36px);
            font-weight: 800;
            line-height: 1.2;
        }

        .tb-dmca-updated {
            margin: 0;
            color: #617168;
            font-size: 13px;
            line-height: 1.6;
        }

        .tb-dmca-section {
            margin-top: 18px;
            padding: 22px 24px;
            border: 1px solid #e0e6e2;
            border-radius: 8px;
            background: #fff;
        }

        .tb-dmca h2 {
            margin: 0 0 12px;
            padding-left: 12px;
            border-left: 3px solid var(--dmca-green);
            color: #14251b;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.4;
        }

        .tb-dmca-section p {
            margin: 0 0 16px;
            font-size: 15px;
            line-height: 1.9;
        }

        .tb-dmca-section p:last-child {
            margin-bottom: 0;
        }

        .tb-dmca-subject {
            padding: 12px 16px;
            border-left: 3px solid var(--dmca-green);
            border-radius: 4px;
            background: #f3f8f5;
            font-weight: 700;
        }

        .tb-dmca-contact {
            border-color: #dce8df;
            background: #f3f8f5;
        }

        .tb-dmca a {
            color: #0066cc;
            text-decoration: none;
        }

        .tb-dmca a:hover {
            text-decoration: underline;
        }

        .tb-dmca a:focus-visible {
            outline: 3px solid var(--dmca-green);
            outline-offset: 4px;
        }

        .tb-dmca .tb-dmca-button {
            display: inline-block;
            max-width: 100%;
            margin-top: 4px;
            padding: 12px 18px;
            border-radius: 5px;
            background: var(--dmca-green);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.5;
            text-align: center;
            text-decoration: none;
        }

        .tb-dmca .tb-dmca-button:hover {
            background: #146c43;
            text-decoration: none;
        }

        @media (max-width: 600px) {
            .tb-dmca {
                margin: 18px auto;
                padding: 0 12px;
            }

            .tb-dmca-header {
                padding: 20px 16px;
            }

            .tb-dmca-section {
                padding: 18px 16px;
            }

            .tb-dmca h2 {
                font-size: 18px;
            }

            .tb-dmca .tb-dmca-button {
                width: 100%;
            }
        }
    </style>

    <article class="tb-dmca">
        <header class="tb-dmca-header">
            <p class="tb-dmca-label">TrendyBeatz Media</p>

            <h1>DMCA — Copyright Policy</h1>

            <p class="tb-dmca-updated">
                Last updated {{ date('F Y') }}
            </p>
        </header>

        <section class="tb-dmca-section">
            <h2>DMCA Removal Request</h2>

            <p>
                Are you a copyright holder who believes your
                copyright has been infringed upon in any way by
                our website? Feel free to email us with the details
                of the copyright infringement, and we will
                immediately identify and remove the infringing material.
            </p>

            <p>
                We accept submissions from various artists and
                their official representatives, record labels,
                etc. If you believe an infringement of your
                copyright has occurred on our website, this page
                is for you.
            </p>
        </section>

        <section class="tb-dmca-section">
            <h2>Before Submitting a Request</h2>

            <p>
                First, be sure that you own the copyrights to the
                work you are claiming is being infringed upon on
                our website. You can consult an attorney to ensure
                the validity of your copyright claims.
            </p>

            <p>
                Please do not file for the removal of content you
                do not own directly or for which you are not the
                legal or appointed representative of the copyright holder.
            </p>
        </section>

        <section class="tb-dmca-section">
            <h2>How to Submit Your Request</h2>

            <p>
                When sending the copyright infringement email,
                please use the following subject:
            </p>

            <p class="tb-dmca-subject">
                Attention: Copyright Infringement
            </p>

            <p>
                Include the links to the infringing material so
                we can identify and remove it immediately.
            </p>
        </section>

        <section class="tb-dmca-section tb-dmca-contact">
            <h2>Copyright Removal Contact</h2>

            <p>
                Send your copyright removal requests to
                <a href="mailto:info@trendybeatz.com">
                    info@trendybeatz.com
                </a>.
            </p>

            <a
                class="tb-dmca-button"
                href="mailto:info@trendybeatz.com?subject=Attention%3A%20Copyright%20Infringement"
            >
                Email a Copyright Removal Request
            </a>
        </section>
    </article>
@endsection