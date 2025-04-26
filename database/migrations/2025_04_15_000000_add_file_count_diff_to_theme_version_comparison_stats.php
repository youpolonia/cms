<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('theme_version_comparison_stats', function (Blueprint $table) {
            $table->integer('file_count_diff')->default(0)->after('files_modified');
        });
    }

    public function down()
    {
        Schema::table('theme_version_comparison_stats', function (Blueprint $table) {
            $table->dropColumn('file_count_diff');
        });
    }
};
