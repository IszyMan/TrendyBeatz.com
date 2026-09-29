<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listing', function (Blueprint $table) {
            $table->text('youtube_embed_url')->nullable();
            $table->text('audiomack_embed_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('listing', function (Blueprint $table) {
            $table->dropColumn([
                'youtube_embed_url',
                'audiomack_embed_url',
            ]);
        });
    }
};