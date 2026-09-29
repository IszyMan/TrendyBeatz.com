<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listing', function (Blueprint $table) {
            // Nullable because imported listings have no creator user_id.
            $table->unsignedInteger('user_id')
                ->nullable()
                ->index()
                ->after('posted_by');
        });
    }

    public function down(): void
    {
        Schema::table('listing', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};