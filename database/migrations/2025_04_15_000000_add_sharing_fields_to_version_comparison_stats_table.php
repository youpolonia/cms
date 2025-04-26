<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('version_comparison_stats', function (Blueprint $table) {
            $table->integer('share_count')->default(0)->after('cache_size_bytes');
            $table->string('share_token')->nullable()->unique()->after('share_count');
            $table->timestamp('shared_at')->nullable()->after('share_token');
            $table->integer('share_access_count')->default(0)->after('shared_at');
        });
    }

    public function down()
    {
        Schema::table('version_comparison_stats', function (Blueprint $table) {
            $table->dropColumn(['share_count', 'share_token', 'shared_at', 'share_access_count']);
        });
    }
};
