@extends('layouts.app')

@php
    $pageTitle = 'Advertise With Us — TrendyBeatz';

    $metaDescription = 'Advertise your business on TrendyBeatz. Explore audience statistics, banner placements and contact our advert team for pricing and bookings.';

    $metaKeywords = 'advertise on TrendyBeatz, TrendyBeatz advertising, banner advertising, Nigerian audience, African audience, advert placement';

    $canonical = route('page.advertise');

    $audienceCountries = [
        'Nigeria' => '55%',
        'Ghana' => '11%',
        'Zambia' => '5%',
        'Tanzania' => '5%',
        'South Africa' => '5%',
        'Uganda' => '3%',
        'United States' => '3%',
        'India' => '3%',
        'Kenya' => '3%',
        'Malawi' => '1%',
    ];
@endphp

@section('title', $pageTitle)
@section('meta_keywords', $metaKeywords)
@section('meta_description', $metaDescription)
@section('canonical', $canonical)
@section('social_title', $pageTitle)
@section('social_description', $metaDescription)

@section('content')
    <style>
        .tb-advertise {
            --advert-green: #198754;
            width: 100%;
            max-width: 900px;
            margin: 28px auto;
            padding: 0 16px;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #25332b;
            overflow-wrap: anywhere;
        }

        .tb-advertise * {
            box-sizing: border-box;
        }

        .tb-advertise-header {
            padding: 26px;
            border: 1px solid #dce8df;
            border-top: 4px solid var(--advert-green);
            border-radius: 8px;
            background: #f3f8f5;
        }

        .tb-advertise-label {
            margin: 0 0 10px;
            color: var(--advert-green);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        .tb-advertise h1 {
            margin: 0 0 12px;
            color: #14251b;
            font-size: clamp(27px, 4vw, 36px);
            font-weight: 800;
            line-height: 1.2;
        }

        .tb-advertise-updated {
            margin: 0;
            color: #617168;
            font-size: 13px;
            line-height: 1.6;
        }

        .tb-advertise-intro {
            margin: 24px 0;
        }

        .tb-advertise p {
            margin: 0 0 16px;
            font-size: 15px;
            line-height: 1.9;
        }

        .tb-advertise-header .tb-advertise-label {
            font-size: 12px;
            line-height: 1.5;
        }

        .tb-advertise-header .tb-advertise-updated {
            margin: 0;
            font-size: 13px;
            line-height: 1.6;
        }

        .tb-advertise-section {
            margin-top: 18px;
            padding: 22px 24px;
            border: 1px solid #e0e6e2;
            border-radius: 8px;
            background: #fff;
        }

        .tb-advertise h2 {
            margin: 0 0 16px;
            padding-left: 12px;
            border-left: 3px solid var(--advert-green);
            color: #14251b;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.4;
        }

        .tb-advertise-section p:last-child {
            margin-bottom: 0;
        }

        .tb-advertise-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .tb-advertise-stat {
            padding: 20px 12px;
            border: 1px solid #dce8df;
            border-radius: 6px;
            background: #f3f8f5;
            text-align: center;
        }

        .tb-advertise-stat strong {
            display: block;
            margin-bottom: 6px;
            color: var(--advert-green);
            font-size: 28px;
            line-height: 1.2;
        }

        .tb-advertise-stat span {
            font-size: 13px;
            line-height: 1.5;
        }

        .tb-advertise-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 15px;
        }

        .tb-advertise-table th,
        .tb-advertise-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #e0e6e2;
            text-align: left;
        }

        .tb-advertise-table th {
            background: #f3f8f5;
            color: #14251b;
            font-weight: 700;
        }

        .tb-advertise-table th:last-child,
        .tb-advertise-table td:last-child {
            text-align: right;
        }

        .tb-advertise-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .tb-advertise-formats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .tb-advertise-formats li {
            padding: 14px 16px;
            border: 1px solid #dce8df;
            border-radius: 6px;
            background: #f3f8f5;
            font-size: 15px;
            font-weight: 600;
            line-height: 1.5;
        }

        .tb-advertise-price {
            color: var(--advert-green);
            font-weight: 800;
        }

        .tb-advertise-contact {
            border-color: #dce8df;
            background: #f3f8f5;
        }

        .tb-advertise a {
            color: #0066cc;
            text-decoration: none;
        }

        .tb-advertise a:hover {
            text-decoration: underline;
        }

        .tb-advertise a:focus-visible {
            outline: 3px solid var(--advert-green);
            outline-offset: 4px;
        }

        .tb-advertise .tb-advertise-button {
            display: inline-block;
            max-width: 100%;
            margin-top: 4px;
            padding: 12px 18px;
            border-radius: 5px;
            background: var(--advert-green);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.5;
            text-align: center;
            text-decoration: none;
        }

        .tb-advertise .tb-advertise-button:hover {
            background: #146c43;
            text-decoration: none;
        }

        @media (max-width: 600px) {
            .tb-advertise {
                margin: 18px auto;
                padding: 0 12px;
            }

            .tb-advertise-header {
                padding: 20px 16px;
            }

            .tb-advertise-section {
                padding: 18px 16px;
            }

            .tb-advertise h2 {
                font-size: 18px;
            }

            .tb-advertise-stats,
            .tb-advertise-formats {
                grid-template-columns: 1fr;
            }

            .tb-advertise-stat {
                padding: 16px;
            }

            .tb-advertise .tb-advertise-button {
                width: 100%;
            }
        }
    </style>

    <article class="tb-advertise">
        <header class="tb-advertise-header">
            <p class="tb-advertise-label">TrendyBeatz Media</p>

            <h1>Advertise on TrendyBeatz</h1>

            <p class="tb-advertise-updated">
                Last updated {{ date('F Y') }}
            </p>
        </header>

        <div class="tb-advertise-intro">
            <p>
                TrendyBeatz is a legal music discovery and entertainment
                website with millions of monthly users and tens of
                millions of monthly impressions. We can give your
                business the publicity it needs to thrive.
            </p>

            <p>
                Music is food for the soul. Everyone listens to music,
                and our audience includes people with a wide range of
                interests. Your target audience is here.
            </p>

            <p>
                By promoting your business on TrendyBeatz, you can reach
                Nigerian and African audiences who love music and
                entertainment. We have coverage across African countries,
                with additional reach in Europe, Asia and America.
            </p>
        </div>

        <section class="tb-advertise-section">
            <h2>Visitors Statistics</h2>

            <div class="tb-advertise-stats">
                <div class="tb-advertise-stat">
                    <strong>5 Million+</strong>
                    <span>Monthly Users</span>
                </div>

                <div class="tb-advertise-stat">
                    <strong>15 Million+</strong>
                    <span>Monthly Pageviews</span>
                </div>

                <div class="tb-advertise-stat">
                    <strong>500,000+</strong>
                    <span>Daily Pageviews</span>
                </div>
            </div>
        </section>

        <section class="tb-advertise-section">
            <h2>Visitors Country Breakdown</h2>

            <table class="tb-advertise-table">
                <thead>
                    <tr>
                        <th scope="col">Country</th>
                        <th scope="col">Share of Users</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($audienceCountries as $country => $percentage)
                        <tr>
                            <td>{{ $country }}</td>
                            <td>{{ $percentage }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section class="tb-advertise-section">
            <h2>Advert Placement and Rates</h2>

            <p>
                We offer fixed advert rates that allow your advert
                to reach either our whole traffic or half of our traffic.
            </p>

            <p>
                This arrangement is called a takeover because a placement
                position is allocated solely to you, with no other advert
                in rotation for the duration of our contract.
            </p>
        </section>

        <section class="tb-advertise-section">
            <h2>Advert Formats</h2>

            <ul class="tb-advertise-formats">
                <li>Header Placement</li>
                <li>Sticky Footer</li>
                <li>Sitewide Rectangle Banner</li>
                <li>In-post Rectangle</li>
            </ul>
        </section>

        <section class="tb-advertise-section">
            <h2>Banner Sizes and Advert Guidelines</h2>

            <p>
                We currently accept all banner sizes and placements,
                except pop-up ads.
            </p>

            <p>
                We accept all adverts excluding pornographic adverts.
            </p>
        </section>

        <section class="tb-advertise-section">
            <h2>Banner Image Editing</h2>

            <p>
                If you do not have an advert banner, we can create
                one to advertise your business.
            </p>

            <p class="tb-advertise-price">
                Cost: ₦30,000 ($50)
            </p>
        </section>

        <section class="tb-advertise-section tb-advertise-contact">
            <h2>Contact Our Advert Team</h2>

            <p>
                To discuss pricing and book an advert, contact our
                advert team on WhatsApp with your banner and desired
                placement so we can send you our rates.
            </p>

            <p>
                <strong>Call and WhatsApp for Nigerians:</strong><br>
                <a href="tel:+2349076131844">09076131844</a>,
                <a href="tel:+2347067455144">07067455144</a>
            </p>

            <p>
                <strong>Call and WhatsApp internationally:</strong><br>
                <a href="tel:+2349076131844">+2349076131844</a>
            </p>

            <a
                class="tb-advertise-button"
                href="https://wa.me/2349076131844"
                target="_blank"
                rel="nofollow noopener noreferrer"
            >
                Contact the Advert Team on WhatsApp
            </a>
        </section>
    </article>
@endsection