<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('shared_access_logs', function (Blueprint $table) {
            $table->string('notification_email')->nullable()->after('metadata');
            $table->timestamp('notification_sent_at')->nullable();
            $table->boolean('notification_success')->default(false);
            $table->text('notification_error')->nullable();
        });
    }

    public function down()
    {
        Schema::table('shared_access_logs', function (Blueprint $table) {
            $table->dropColumn([
                'notification_email',
                'notification_sent_at',
                'notification_success', 
                'notification_error'
            ]);
        });
    }
};
