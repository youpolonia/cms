<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Update existing users to have default export notification preferences
        \DB::table('users')->update([
            'notification_preferences' => \DB::raw(
                "JSON_SET(
                    COALESCE(notification_preferences, '{}'),
                    '$.analytics_export_ready', true,
                    '$.analytics_export_deleted', true
                )"
            )
        ]);
    }

    public function down()
    {
        // Remove the preferences if rolling back
        \DB::table('users')->update([
            'notification_preferences' => \DB::raw(
                "JSON_REMOVE(
                    COALESCE(notification_preferences, '{}'),
                    '$.analytics_export_ready',
                    '$.analytics_export_deleted'
                )"
            )
        ]);
    }
};
