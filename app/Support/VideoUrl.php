<?php

namespace App\Support;

use Illuminate\Support\Str;

class VideoUrl
{
    public static function slug(object $video): string
    {
        $artist = trim((string) (
            $video->artist_name
            ?? $video->Stage_Name
            ?? $video->ArtistsName
            ?? ''
        ));

        $title = trim((string) (
            $video->track_title
            ?? $video->TrackTitle
            ?? ''
        ));

        $featuring = trim((string) (
            $video->featuring
            ?? $video->Featuring
            ?? ''
        ));

        $name = trim($artist . ' ' . $title);

        if (
            $featuring !== ''
            && !preg_match('/\b(?:ft|feat|featuring)\.?\s/i', $title)
        ) {
            $name .= ' ft ' . $featuring;
        }

        return Str::slug($name);
    }

    public static function detail(object $video): string
    {
        return route('video_details', [
            $video->id,
            self::slug($video),
        ]);
    }
}