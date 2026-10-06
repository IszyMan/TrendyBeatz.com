<footer class="site-footer">
    <div class="footer-columns">
        <section>
            <h2>Quick Link</h2>

            <a href="{{ route('songs.day') }}">
                Top Ten Music
            </a>

            <a href="{{ route('videos.index') }}">
                Latest Videos
            </a>

            <a href="{{ route('music.naija') }}">
                Discover Latest Music
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

       <section class="footer-address" style="line-height: 1.7;">
            <h2 style="margin-bottom: 12px; font-size: 20px;">
                Address
            </h2>
        
            <p style="margin-bottom: 14px;">
                Lekki Peninsula,<br>
                Eti-osa Lagos, Nigeria
            </p>
        
            <p style="margin-bottom: 6px; font-weight: 500;">
                For advert and business enquiries:
            </p>
        
            <p style="margin-bottom: 10px;">
               <b style="color:yellow; font-size:20px"> Send us a mail:</b>
                <a
                    href="mailto:info@trendybeatz.com"
                    style="color: inherit; text-decoration: underline; text-underline-offset: 3px;"
                >
                    info@trendybeatz.com
                </a>
            </p>
        
            <p style="display: block; margin: 0 0 14px; line-height: 1.3;">
                Or Whatsapp/ Call<br>
                <a href="tel:07067455155" style="display: inline !important; margin: 0 !important; padding: 0 !important; color: inherit; text-decoration: none;">07067455144</a><br>
                <a href="tel:09034211133" style="display: inline !important; margin: 0 !important; padding: 0 !important; color: inherit; text-decoration: none;">09076131844</a>
            </p>
                        
            <a
                href="https://wa.me/2349076131844"
                style="display: inline-block; background-color: #25d366; color: #fff; padding: 6px 12px; border: none; border-radius: 4px; text-decoration: none; font-size: 14px;"
            >
                Contact Us on WhatsApp
            </a>
        
            <button
                type="button"
                class="back-to-top"
                onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
                style="display: block; margin-top: 18px; padding: 0; background: transparent; border: none; color: inherit; font-size: 13px; cursor: pointer;"
            >
                Back to Top ↑
            </button>
        </section>
    </div>

    <div class="footer-copyright">
        Copyright {{ date('Y') }} © TrendyBeatz Media
    </div>
</footer>