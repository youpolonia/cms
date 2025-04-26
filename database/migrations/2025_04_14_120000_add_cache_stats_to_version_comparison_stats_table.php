<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('version_comparison_stats', function (Blueprint $table) {
            $table->integer('cache_hits')->default(0)->after('files_changed');
            $table->integer('cache_misses')->default(0)->after('cache_hits');
            $table->timestamp('last_cached_at')->nullable()->after('cache_misses');
            $table->integer('cache_size_bytes')->default(0)->after('last_cached_at');
        });
    }

    public function down()
    {
        Schema::table('version_comparison_stats', function (Blueprint $table) {
            $table->dropColumn(['cache_hits', 'cache_misses', 'last_cached_at', 'cache_size_bytes']);
        });
    }
};
