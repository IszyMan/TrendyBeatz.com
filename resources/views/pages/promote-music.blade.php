@extends('layouts.app')

@section('title', 'Music Promotion — TrendyBeatz')

@section('content')
    @php
        $categoryPlacement = 'Upload to the Naija, Ghana or African Music category, depending on your target audience and country.';

        $rotationPlacement = 'Your song is featured in the middle of music and video posts for one month, rotating with other songs on the same package. This placement receives 500,000 to 1 million daily shared views and about 30 million monthly views.';

        $storeLink = 'Include a link to YouTube, Fanlink or your digital stores so visitors can watch or listen to your song.';

        $packages = [
            [
                'name' => 'Regular Package',
                'price' => '₦30,000 ($70)',
                'features' => [$categoryPlacement],
            ],
            [
                'name' => 'Regular PRO Package',
                'price' => '₦50,000 ($80)',
                'features' => [
                    $categoryPlacement,
                    'Include a digital or streaming store link alongside the MP3 download, or exclude the MP3 download entirely.',
                ],
            ],
            [
                'name' => 'Premium Package',
                'price' => '₦80,000 ($100)',
                'features' => [
                    $categoryPlacement,
                    'Placement on Song of the Day for one week.',
                ],
            ],
            [
                'name' => 'GOLD Package',
                'price' => '₦100,000 ($150)',
                'features' => [
                    $categoryPlacement,
                    'Placement on Song of the Day for one week.',
                    'Placement on Song of the Week for one week.',
                ],
            ],
            [
                'name' => 'PLATINUM Package',
                'price' => '₦150,000 ($250)',
                'features' => [
                    $categoryPlacement,
                    'Placement on Song of the Day for one month.',
                    'Placement on Song of the Week for one month.',
                ],
            ],
            [
                'name' => 'SONG ROTATION Package',
                'price' => '₦250,000 ($500)',
                'features' => [
                    $categoryPlacement,
                    $rotationPlacement,
                ],
            ],
            [
                'name' => 'SONG ROTATION PLUS Package',
                'price' => '₦300,000 ($600)',
                'features' => [
                    $categoryPlacement,
                    $storeLink,
                    $rotationPlacement,
                ],
            ],
            [
                'name' => 'SONG ROTATION PRO Package',
                'price' => '₦500,000 ($700)',
                'features' => [
                    $categoryPlacement,
                    $storeLink,
                    $rotationPlacement,
                    'A prominent spot on Song of the Day for one month.',
                    'A prominent spot on Song of the Week for one month.',
                ],
            ],
            [
                'name' => 'EP Package',
                'price' => '₦100,000 ($150)',
                'features' => [
                    $categoryPlacement,
                    'A dedicated EP album page on TrendyBeatz.',
                    'This price applies to projects containing two to six tracks.',
                ],
            ],
            [
                'name' => 'Album Package',
                'price' => '₦200,000 ($300)',
                'features' => [
                    $categoryPlacement,
                    'A dedicated album page on TrendyBeatz.',
                ],
            ],
            [
                'name' => 'VIDEO Upload',
                'price' => '₦50,000 ($100)',
                'features' => [],
            ],
            [
                'name' => 'DJ Mix Promotional Package',
                'price' => '₦30,000 ($70)',
                'features' => [],
            ],
            [
                'name' => 'Artwork Editing',
                'price' => '₦15,000 ($20)',
                'features' => [
                    'We can create artwork for you if you have none.',
                ],
            ],
        ];
    @endphp

    <article class="tb-static-page tb-promote-page">
        <header class="tb-static-header">
            <h1>Music Promotion</h1>
        </header>

        <div class="tb-static-content">
            <p>
                Thank you for your interest in promoting your music on
                TrendyBeatz.com — #1 Naija Music Museum.
                We are delighted you chose us.
            </p>

            <p>
                Promoting your music on TrendyBeatz helps you reach a wide
                range of Nigerian and African audiences who love music
                and entertainment.
            </p>

            <p>
                We offer a broad range of online music and content
                promotion options, outlined below.
            </p>

            <section class="tb-static-section">
                <h2>TrendyBeatz.com Visitors Statistics</h2>

                <ul>
                    <li>
                        <strong>Monthly users:</strong>
                        5 million+ music and entertainment lovers.
                    </li>
                    <li>
                        <strong>Monthly pageviews/downloads:</strong>
                        30 million+.
                    </li>
                </ul>
            </section>

            <section class="tb-static-section">
                <h2>TrendyBeatz Promotional Packages</h2>

                <div class="tb-promote-packages">
                    @foreach ($packages as $package)
                        <section class="tb-promote-package">
                            <h3>
                                {{ $loop->iteration }}.
                                {{ $package['name'] }}
                            </h3>

                            <p class="tb-promote-price">
                                <strong>Cost:</strong>
                                {{ $package['price'] }}
                            </p>

                            @if ($package['features'])
                                <ol>
                                    @foreach ($package['features'] as $feature)
                                        <li>{{ $feature }}</li>
                                    @endforeach
                                </ol>
                            @endif
                        </section>
                    @endforeach
                </div>

                <p class="tb-static-note">
                    <strong>Note:</strong>
                    Our prices are subject to change at any time,
                    without notice.
                </p>
            </section>

            <section class="tb-static-section">
                <h2>Information to Send for Your Song Promotion</h2>

                <ol>
                    <li>
                        Your biography, following the format of the
                        artiste biographies on our website.
                    </li>
                    <li>
                        Your best professional photograph for your biography.
                    </li>
                    <li>
                        Your song, track title, stage name, artwork,
                        producer (optional) and release date.
                    </li>
                </ol>

                <p class="tb-static-note">
                    <strong>Image size:</strong>
                    All images should be less than 1MB.
                </p>
            </section>

            <section class="tb-static-section">
                <h2>How to Make Payment</h2>

                <p>
                    Contact us on WhatsApp using the link below and tell
                    us which package you would like to pay for.
                    We will send you the bank account details for payment.
                </p>

                <p>
                    <strong>
                        Contact us only when you are ready to make
                        payment and promote your music.
                    </strong>
                </p>
            </section>

            <section class="tb-static-section tb-promote-contact">
                <h2>TrendyBeatz.com Contact</h2>

                <p>
                    <strong>Call and WhatsApp for Nigerians:</strong>
                    <a href="tel:+2349076131844">09076131844</a>,
                    <a href="tel:+2347067455144">07067455144</a>
                </p>

                <p>
                    <strong>Call and WhatsApp internationally:</strong>
                    <a href="tel:+2349076131844">+2349076131844</a>
                </p>

                <a
                    class="tb-static-button"
                    href="https://wa.me/2349076131844"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Contact the Advert Team on WhatsApp
                </a>
            </section>

            <section class="tb-static-section">
                <h2>Release and Publishing Schedule</h2>

                <p>
                    Contact us and make payment one or two days before
                    your intended release date so we have time to
                    prepare for your song release.
                </p>

                <p>
                    Songs are not published immediately upon receipt.
                    We carry out quality checks before publishing.
                    Please allow 24 hours after sending all the
                    required details.
                </p>

                <p>
                    We may adjust the schedule and make exceptions
                    depending on the urgency of the release.
                </p>

                <p><strong>Thank you for your patronage!</strong></p>
            </section>
        </div>
    </article>
@endsection