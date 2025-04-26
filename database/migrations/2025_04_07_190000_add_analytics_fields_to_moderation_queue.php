<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('moderation_queue', function (Blueprint $table) {
            $table->json('analytics')->nullable()->after('status');
            $table->string('template_id')->nullable()->after('analytics');
            $table->string('template_type')->nullable()->after('template_id');
        });
    }

    public function down()
    {
        Schema::table('moderation_queue', function (Blueprint $table) {
            $table->dropColumn(['analytics', 'template_id', 'template_type']);
        });
    }
};