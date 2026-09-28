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
        </section>

        <section>
            <h2>Explore</h2>

            <a href="{{ route('music.all') }}">
                All Music
            </a>

            <a href="{{ route('music.ghana') }}">
                Ghana Music
            </a>

            <a href="{{ route('music.african') }}">
                African Music
            </a>

            <a href="{{ route('blogs.index') }}">
                News & Blog
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