<?php

namespace App\Support;

use Illuminate\Support\Str;

class DjMixUrl
{
    public static function slug(object $mix): string
    {
        $djName = trim((string) ($mix->dj_name ?? ''));

        if ($djName === '') {
            $djName = 'TrendyBeatz DJ';
        }

        $mixTitle = trim((string) ($mix->mix_title ?? ''));

        return Str::slug($djName . ' ' . $mixTitle);
    }

    public static function detail(object $mix): string
    {
        return route('mixes.show', [
            $mix->id,
            self::slug($mix),
        ]);
    }
}