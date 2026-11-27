<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('watch_links', function (Blueprint $table) {
            $table->string('title', 255)->nullable()->after('media_type');
            $table->string('image_url', 500)->nullable()->after('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('watch_links', function (Blueprint $table) {
            $table->dropColumn(['title', 'image_url']);
        });
    }
};
