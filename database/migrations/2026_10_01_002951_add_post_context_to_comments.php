<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->string('post_type', 20)->nullable();
            $table->unsignedBigInteger('post_id')->nullable();
            $table->string('post_title', 500)->nullable();
            $table->string('post_path', 2048)->nullable();

            $table->index(
                ['post_type', 'post_id', 'is_allowed'],
                'comments_post_approval'
            );
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('comments_post_approval');

            $table->dropColumn([
                'post_type',
                'post_id',
                'post_title',
                'post_path',
            ]);
        });
    }
};