<footer class="site-footer">
    <div class="footer-columns">
        <section>
            <h2>Quick Link</h2>

            <a href="{{ route('home') }}#song-of-the-week">
                Top Ten Music
            </a>

            <a href="{{ route('videos.index') }}">
                Latest Videos
            </a>

            <a href="{{ route('music.naija') }}">
                Download Musics
            </a>

            <a href="{{ route('blogs.category', 'celebrity-news') }}">
                Celebrity News
            </a>

            <a href="{{ route('page.promote') }}">
                Upload Your Songs
            </a>

            <a href="{{ route('page.promote') }}">
                Music Promotion
            </a>
        </section>

        <section>
            <h2>Hall of Fame</h2>

            <a href="{{ route('artists.index') }}">
                Artiste Profile
            </a>

            <a href="{{ route('albums.index') }}">
                Artiste Album
            </a>

            <a href="{{ route('blogs.category', 'hot-gists') }}">
                Hot Gist
            </a>

            <a href="{{ route('mixes.index') }}">
                DJ Mix
            </a>

            <a href="{{ route('djs.index') }}">
                DJs Profile
            </a>
        </section>

        <section>
            <h2>Company</h2>

            <a href="{{ route('page.about') }}">
                About Us
            </a>

            <a href="{{ route('page.terms') }}">
                Terms of Use
            </a>

            <a href="{{ route('page.privacy') }}">
                Privacy Policy
            </a>

            <a href="{{ route('page.contact') }}">
                Contact Us
            </a>

            <a href="{{ route('page.advertise') }}">
                Advertise With Us
            </a>

            <a href="{{ route('page.dmca') }}">
                DMCA
            </a>

            <a href="{{ route('page.disclaimer') }}">
                Disclaimer
            </a>
        </section>

        <section class="footer-address">
            <h2>Address</h2>

            <p>Lekki Peninsula<br>Lekki, Lagos, Nigeria</p>

            <p>For advert and business enquiries:</p>

            <a href="mailto:info@trendybeatz.com">
                info@trendybeatz.com
            </a>

            <a href="https://wa.me/2349076131844">
                Contact Us on WhatsApp
            </a>

            <button
                type="button"
                class="back-to-top"
                onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
            >
                Back to Top ↑
            </button>
        </section>
    </div>

    <div class="footer-copyright">
        Copyright {{ date('Y') }} © TrendyBeatz Media
    </div>
</footer>