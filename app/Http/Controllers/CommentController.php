<?php

namespace App\Http\Controllers;

use App\Support\CommentTarget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->merge([
            'name' => is_string($request->input('name'))
                ? trim($request->input('name'))
                : $request->input('name'),

            'message' => is_string($request->input('message'))
                ? trim($request->input('message'))
                : $request->input('message'),
        ]);

        $plainText = static function (
            $attribute,
            $value,
            $fail
        ) {
            $pattern = '~(?:'
                . '[a-z][a-z0-9+.-]*\s*:\s*/\s*/'
                . '|\bwww\s*\.'
                . '|\b(?:mailto|javascript|data)\s*:'
                . '|\b[\pL\pN][\pL\pN.-]*\.[a-z]{2,63}(?:\b|/)'
                . '|<[^>]*>'
                . ')~iu';

            if (preg_match($pattern, $value)) {
                $fail(
                    'Links and HTML are not allowed. '
                    . 'Please enter plain text.'
                );
            }
        };

        $data = $request->validate([
            'target' => [
                'required',
                'string',
                'max:10000',
            ],

            'name' => [
                'bail',
                'required',
                'string',
                'max:191',
                $plainText,
            ],

            'email' => [
                'nullable',
                'string',
                'email',
                'max:191',
            ],

            'message' => [
                'bail',
                'required',
                'string',
                'min:2',
                'max:3000',
                $plainText,
            ],
        ], [
            'name.required' => 'Please enter your name.',
            'message.required' => 'Please write a comment.',
        ]);

        $target = CommentTarget::decode($data['target']);

        DB::table('comments')->insert([
            'blog_id' => $target['type'] === 'blog'
                ? $target['id']
                : 0,

            'category_id' => $target['type'],

            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'coments' => $data['message'],

            'is_allowed' => 0,

            'post_type' => $target['type'],
            'post_id' => $target['id'],
            'post_title' => $target['title'],
            'post_path' => $target['path'],

            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Your message has been submitted '
                . 'and is waiting for approval.',
        ], 201);
    }
}