<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('approval_workflows', function (Blueprint $table) {
            $table->json('time_metrics')->nullable()->after('type');
            $table->json('notification_settings')->nullable()->after('time_metrics');
        });
    }

    public function down()
    {
        Schema::table('approval_workflows', function (Blueprint $table) {
            $table->dropColumn(['time_metrics', 'notification_settings']);
        });
    }
};
