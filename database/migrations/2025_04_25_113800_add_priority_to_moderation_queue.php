<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('moderation_queue', function (Blueprint $table) {
            $table->integer('priority')->default(0)->after('status')->index();
            $table->json('automated_flags')->nullable()->after('moderation_metadata');
            $table->string('flag_severity')->nullable()->after('automated_flags')->index();
        });
    }

    public function down()
    {
        Schema::table('moderation_queue', function (Blueprint $table) {
            $table->dropColumn(['priority', 'automated_flags', 'flag_severity']);
        });
    }
};