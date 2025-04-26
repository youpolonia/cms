<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->unsignedInteger('restore_count')->default(0)->after('engagement_score');
        });

        Schema::table('content_versions', function (Blueprint $table) {
            $table->unsignedInteger('restore_count')->default(0)->after('tags');
        });
    }

    public function down()
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn('restore_count');
        });

        Schema::table('content_versions', function (Blueprint $table) {
            $table->dropColumn('restore_count');
        });
    }
};
