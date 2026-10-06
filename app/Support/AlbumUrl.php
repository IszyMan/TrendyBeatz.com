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

        // Song records need the album's featuring, not the song's.
        $featuring = trim((string) (
            isset($item->album_id)
                ? ($item->album_featuring ?? '')
                : ($item->featuring ?? $item->Featuring ?? '')
        ));

        $name = trim($artist . ' ' . $title);

        if ($featuring !== '') {
            $name .= ' ft ' . $featuring;
        }

        return Str::slug($name);
    }

    public static function detail(object $item): string
    {
        $albumId = $item->album_id ?? $item->id;

        return route('albums.show', [
            $albumId,
            self::slug($item),
        ]);
    }
}