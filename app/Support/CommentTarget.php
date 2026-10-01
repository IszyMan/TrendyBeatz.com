<?php

namespace App\Support;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;

class CommentTarget
{
    public static function token(
        string $type,
        int $id,
        string $title
    ): string {
        return Crypt::encryptString(
            json_encode([
                'type' => $type,
                'id' => $id,
                'title' => mb_substr($title, 0, 500),
                'path' => '/' . ltrim(request()->path(), '/'),
                'expires' => now()->addDay()->timestamp,
            ], JSON_THROW_ON_ERROR)
        );
    }

    public static function decode(string $token): array
    {
        try {
            $target = json_decode(
                Crypt::decryptString($token),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'target' => 'Please refresh this page and try again.',
            ]);
        }

        if (
            !is_array($target)
            || !in_array(
                $target['type'] ?? null,
                ['music', 'video', 'djmix', 'blog', 'album'],
                true
            )
            || !is_int($target['id'] ?? null)
            || $target['id'] < 1
            || !is_string($target['title'] ?? null)
            || !is_string($target['path'] ?? null)
            || !str_starts_with($target['path'], '/')
            || str_starts_with($target['path'], '//')
            || !is_int($target['expires'] ?? null)
            || $target['expires'] < time()
        ) {
            throw ValidationException::withMessages([
                'target' => 'Please refresh this page and try again.',
            ]);
        }

        return $target;
    }
}