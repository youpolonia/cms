<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('theme_version_comparison_stats', function (Blueprint $table) {
            $table->json('size_breakdown')->nullable()->after('file_size_changes');
        });
    }

    public function down()
    {
        Schema::table('theme_version_comparison_stats', function (Blueprint $table) {
            $table->dropColumn('size_breakdown');
        });
    }
};
