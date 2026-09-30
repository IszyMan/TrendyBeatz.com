<?php

namespace App\Support;

use Illuminate\Support\Str;

class AlbumUrl
{
    public static function slug(object $item): string
    {
        $artist = trim((string) (
            $item->artist_name
            ?? $item->Stage_Name
            ?? $item->ArtistsName
            ?? ''
        ));

        $title = trim((string) (
            $item->album_name
            ?? $item->title
            ?? $item->AlbumName
            ?? ''
        ));

        return Str::slug(trim($artist . ' ' . $title));
    }

    public static function detail(object $item): string
    {
        // Songs contain album_id; album records contain id.
        $albumId = $item->album_id ?? $item->id;

        return route('albums.show', [
            $albumId,
            self::slug($item),
        ]);
    }
}