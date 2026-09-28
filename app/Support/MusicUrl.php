<?php

namespace App\Support;

use Illuminate\Support\Str;

class MusicUrl
{
    public static function slug(object $song): string
    {
        $artist = trim((string) (
            $song->artist_name
            ?? $song->Stage_Name
            ?? $song->ArtistsName
            ?? ''
        ));

        $title = trim((string) (
            $song->track_title
            ?? $song->TrackTitle
            ?? ''
        ));

        $featuring = trim((string) (
            $song->featuring
            ?? $song->Featuring
            ?? ''
        ));

        $name = trim($artist . ' ' . $title);

        // Add featured artistes only when the title does not already name them.
        if (
            $featuring !== ''
            && !preg_match('/\b(?:ft\.?|feat\.?|featuring)\s/i', $title)
        ) {
            $name .= ' ft ' . $featuring;
        }

        return Str::slug($name);
    }

    public static function detail(object $song): string
    {
        return route('music_details', [
            $song->id,
            self::slug($song),
        ]);
    }
}