<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('theme_version_rollbacks', function (Blueprint $table) {
            $table->enum('reason', ['performance', 'stability', 'user_error', 'other'])
                ->nullable()
                ->comment('Primary reason for the rollback');
                
            $table->json('performance_impact')->nullable()
                ->comment('Performance metrics before/after rollback');
                
            $table->json('stability_impact')->nullable()
                ->comment('Stability metrics before/after rollback');
                
            $table->json('notification_preferences')->nullable()
                ->comment('Notification settings for this rollback');
                
            $table->json('user_behavior_metrics')->nullable()
                ->comment('Metrics about user interactions leading to rollback');
        });
    }

    public function down()
    {
        Schema::table('theme_version_rollbacks', function (Blueprint $table) {
            $table->dropColumn([
                'reason',
                'performance_impact',
                'stability_impact',
                'notification_preferences',
                'user_behavior_metrics'
            ]);
        });
    }
};
