<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('content_user_views', function (Blueprint $table) {
            if (!Schema::hasColumn('content_user_views', 'scroll_depth')) {
                $table->integer('scroll_depth')->nullable()->after('viewed_at');
            }
            if (!Schema::hasColumn('content_user_views', 'time_spent')) {
                $table->integer('time_spent')->nullable()->after('scroll_depth');
            }
            if (!Schema::hasColumn('content_user_views', 'interacted')) {
                $table->boolean('interacted')->default(false)->after('time_spent');
            }
        });

        Schema::table('contents', function (Blueprint $table) {
            if (!Schema::hasColumn('contents', 'avg_time_spent')) {
                $table->integer('avg_time_spent')->nullable()->after('view_count');
            }
            if (!Schema::hasColumn('contents', 'completion_rate')) {
                $table->decimal('completion_rate', 5, 2)->nullable()->after('avg_time_spent');
            }
        });

        Schema::create('analytics_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained()->cascadeOnDelete();
            $table->date('snapshot_date');
            $table->integer('view_count')->default(0);
            $table->integer('unique_visitors')->default(0);
            $table->integer('avg_time_spent')->nullable();
            $table->decimal('completion_rate', 5, 2)->nullable();
            $table->json('top_referrers')->nullable();
            $table->timestamps();

            $table->unique(['content_id', 'snapshot_date']);
        });
    }

    public function down()
    {
        Schema::table('content_user_views', function (Blueprint $table) {
            $table->dropColumn(['scroll_depth', 'time_spent', 'interacted']);
        });

        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn(['avg_time_spent', 'completion_rate']);
        });

        Schema::dropIfExists('analytics_snapshots');
    }
};
