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

        $plainText = static function ($attribute, $value, $fail) {
            $check = (string) $value;
        
            // Decode encoded characters for validation only.
            for ($i = 0; $i < 3; $i++) {
                $decoded = html_entity_decode(
                    rawurldecode($check),
                    ENT_QUOTES | ENT_HTML5,
                    'UTF-8'
                );
        
                if ($decoded === $check) {
                    break;
                }
        
                $check = $decoded;
            }
        
            // Normalize full-width characters where intl is available.
            if (class_exists(\Normalizer::class)) {
                $normalized = \Normalizer::normalize(
                    $check,
                    \Normalizer::FORM_KC
                );
        
                if ($normalized !== false) {
                    $check = $normalized;
                }
            }
        
            // Normalize alternative dots and common disguised links.
            $check = str_replace(
                ['。', '．', '｡'],
                '.',
                $check
            );
        
            $check = preg_replace(
                '~(?:\[\s*dot\s*\]|\(\s*dot\s*\))~iu',
                '.',
                $check
            );
        
            // Remove invisible formatting characters.
            // Keep spaces so ordinary sentences are not mistaken for links.
            $check = preg_replace(
                '~\p{Cf}+~u',
                '',
                $check
            );
            
            // Reject phone-like numbers containing 7–15 digits.
            // Supports spaces, brackets, dots, and hyphens.
            $phonePattern = '~(?<![\p{L}\p{N}])'
                . '\+?(?:\(\d{1,4}\)|\d)'
                . '(?:[\s().-]*\d){6,14}'
                . '(?![\p{L}\p{N}])~u';
            
            if (preg_match($phonePattern, $check) === 1) {
                $fail(
                    'Phone numbers are not allowed. Please enter plain text.'
                );
            
                return;
            }
        
            $pattern = '~(?:'
                . 'https?'
                . '|hxxps?'
                . '|www'
                . '|[a-z][a-z0-9+.-]*://'
                . '|(?:mailto|javascript|data):'
                . '|[\p{L}\p{N}][\p{L}\p{N}._-]*\.'
                . '(?:[\p{L}]{2,63}|xn--[a-z0-9-]+)(?![\p{L}\p{N}])'
                . '|(?:\d{1,3}\.){3}\d{1,3}'
                . '|<[^>]*>'
                . ')~iu';
        
            $result = preg_match($pattern, $check);
        
            if ($result === false || $result === 1) {
                $fail(
                    'Links and HTML are not allowed. Please enter plain text.'
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
    
    public function like(int $comment): JsonResponse
    {
        $approvedComment = DB::table('comments')
            ->where('id', $comment)
            ->where('is_allowed', 1);
    
        // Increment in the database so simultaneous clicks are counted correctly.
        $updated = (clone $approvedComment)->increment('likes_count');
    
        abort_if($updated === 0, 404, 'Comment not found.');
    
        $count = (clone $approvedComment)->value('likes_count');
    
        return response()->json([
            'likes_count' => (int) $count,
        ]);
    }
}