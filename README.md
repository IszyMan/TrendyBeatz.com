# TrendyBeatz Laragon starter

This is a Laravel **project overlay**. Create a fresh Laravel application in Laragon, then copy the `app`, `routes`, `resources`, and `public` folders from this package into its root. The package includes its own usable home controller, public routes, responsive Blade templates, and CSS; Composer and Laravel's framework files are created by Laragon on your PC.

## Setup on Windows with Laragon

1. Start Laragon's Apache and MySQL. Open **Laragon → Terminal**. Run:

   ```bat
   cd C:\laragon\www
   composer create-project laravel/laravel trendybeatz-local
   ```

2. Extract this package. Copy its `app`, `resources`, `routes` and `public` folders into `C:\laragon\www\trendybeatz-local\` and accept the merge/overwrite prompt. This replaces the initial `routes/web.php` only. Do **not** put your SQL dump or `.env` in `public`.
3. Create a fresh empty database called `trendybeatz_local` in phpMyAdmin, then import your supplied `tbnaijamtrend_trendybeatz.sql` into it. If phpMyAdmin times out, use Laragon's terminal:

   ```bat
   mysql -u root trendybeatz_local < "C:\path\to\tbnaijamtrend_trendybeatz.sql"
   ```

4. Edit the new project's `.env`:

   ```dotenv
   APP_NAME=TrendyBeatz
   APP_URL=http://trendybeatz-local.test
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=trendybeatz_local
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   Use the actual MySQL username/password from **your** Laragon configuration. Do not share `.env` publicly.

5. In Laragon Terminal, from `C:\laragon\www\trendybeatz-local`, run `php artisan key:generate` and `php artisan optimize:clear`. Do not run `php artisan migrate` after importing the dump: the dump already has tables including `migrations` and `users`.
6. Click **Laragon → Menu → Reload** and open `http://trendybeatz-local.test`. Alternatively run `php artisan serve` and open `http://127.0.0.1:8000`.

## What this restores

- The homepage sections appear in the same order shown by the July 7, 2026 archived page and the supplied `home.blade.php`.
- Sections query the imported `listings`, `artists`, `albums`, `blogs`, `blog_categories`, `djs`, `dj_mixes`, `songs_of_the_day` and `songs_of_the_week` tables.
- Day/week sections use the actual feature tables. Since those feature tables are empty in the supplied dump, their empty messages will show until you assign records. The archive's 2026 featured songs do not exist in the 2023 portion of this database.
- `public/css/trendybeatz.css` styles the new templates. The seven files in `reference/archived-css/` are **original CSS captures**, kept for comparing and further recreating the archived layout. They are not linked to the new templates because those files target older class names.
- `reference/archived-homepage.html` is the supplied snapshot for later reconstruction of exact section markup. Archive playback scripts and ads should not be copied into the live site.

## Missing media and further work

The SQL dump stores **filenames**, not the image, MP3 or video bytes. Place recovered images in `public/images/` using their original names; until then cards show a fallback tile. Playback/downloads remain disabled until actual media is recovered and its use verified. The detail pages, navigation and section queries are a working first pass; admin tools, search, complete historical routes, SEO redirects, paging and detailed article formatting are later phases.

The current templates show trusted legacy blog HTML as originally stored in the database. Before accepting new untrusted article submissions, add HTML sanitization. The imported `users` table may contain password hashes from a different installation; do not enable login with those accounts until you reset credentials.
