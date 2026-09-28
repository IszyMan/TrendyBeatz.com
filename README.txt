TRENDBEATZ NAV UPDATE - LARAGON

1. Extract this ZIP.
2. Copy its app, resources, public, and routes folders into C:\laragon\www\trendybeatz-local\ . Merge folders and replace files when Windows asks.
3. From Laragon Terminal:
   cd C:\laragon\www\trendybeatz-local
   php artisan optimize:clear
   php artisan route:list
4. Refresh http://127.0.0.1:8000 (press Ctrl+F5 if older CSS remains cached).

Do not reimport the SQL or run migrations for this update.

This update restores the original-style navigation, the black/green search row, and the homepage title/date wrapper. It does not reproduce the older homepage item cards or lost original include files yet. The search currently searches published song/video titles and artiste names. Blog category URLs are live: hot-gists reads the hot-topics category in the database.
