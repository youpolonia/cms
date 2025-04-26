<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('content_user_views', function (Blueprint $table) {
            if (!Schema::hasColumn('content_user_views', 'scheduled_publish_at')) {
                $table->timestamp('scheduled_publish_at')->nullable()->after('interacted');
            }
            if (!Schema::hasColumn('content_user_views', 'scheduled_expire_at')) {
                $table->timestamp('scheduled_expire_at')->nullable()->after('scheduled_publish_at');
            }
            if (!Schema::hasColumn('content_user_views', 'publish_delay_seconds')) {
                $table->integer('publish_delay_seconds')->nullable()->after('scheduled_expire_at');
            }
            if (!Schema::hasColumn('content_user_views', 'predicted_engagement_score')) {
                $table->float('predicted_engagement_score')->nullable()->after('publish_delay_seconds');
            }
            if (!Schema::hasColumn('content_user_views', 'scheduling_accuracy')) {
                $table->float('scheduling_accuracy')->nullable()->after('predicted_engagement_score');
            }
            if (!Schema::hasColumn('content_user_views', 'scheduling_type')) {
                $table->string('scheduling_type')->nullable()->after('scheduling_accuracy');
            }
        });
    }

    public function down()
    {
        Schema::table('content_user_views', function (Blueprint $table) {
            $table->dropColumn([
                'scheduled_publish_at',
                'scheduled_expire_at',
                'publish_delay_seconds',
                'predicted_engagement_score',
                'scheduling_accuracy',
                'scheduling_type'
            ]);
        });
    }
};