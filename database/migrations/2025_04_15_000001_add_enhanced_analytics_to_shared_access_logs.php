<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('shared_access_logs', function (Blueprint $table) {
            $table->string('country_code')->nullable()->after('ip_address');
            $table->string('city')->nullable()->after('country_code');
            $table->string('device_type')->nullable()->after('user_agent');
            $table->string('browser')->nullable()->after('device_type');
            $table->string('platform')->nullable()->after('browser');
            $table->unsignedInteger('duration_seconds')->nullable()->after('metadata');
            $table->unsignedInteger('scroll_depth')->nullable()->after('duration_seconds');
            $table->json('interactions')->nullable()->after('scroll_depth');
        });
    }

    public function down()
    {
        Schema::table('shared_access_logs', function (Blueprint $table) {
            $table->dropColumn([
                'country_code',
                'city',
                'device_type',
                'browser',
                'platform',
                'duration_seconds',
                'scroll_depth',
                'interactions'
            ]);
        });
    }
};
