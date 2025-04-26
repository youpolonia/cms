<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('theme_version_comparison_stats', function (Blueprint $table) {
            if (!Schema::hasColumn('theme_version_comparison_stats', 'total_size_diff_kb')) {
                $table->integer('total_size_diff_kb')->default(0)->after('file_count_diff');
            }
            if (!Schema::hasColumn('theme_version_comparison_stats', 'css_size_diff_kb')) {
                $table->integer('css_size_diff_kb')->default(0)->after('total_size_diff_kb');
            }
            if (!Schema::hasColumn('theme_version_comparison_stats', 'js_size_diff_kb')) {
                $table->integer('js_size_diff_kb')->default(0)->after('css_size_diff_kb');
            }
            if (!Schema::hasColumn('theme_version_comparison_stats', 'image_size_diff_kb')) {
                $table->integer('image_size_diff_kb')->default(0)->after('js_size_diff_kb');
            }
            if (!Schema::hasColumn('theme_version_comparison_stats', 'total_size_kb')) {
                $table->integer('total_size_kb')->default(0)->after('image_size_diff_kb');
            }
            if (!Schema::hasColumn('theme_version_comparison_stats', 'css_size_kb')) {
                $table->integer('css_size_kb')->default(0)->after('total_size_kb');
            }
            if (!Schema::hasColumn('theme_version_comparison_stats', 'js_size_kb')) {
                $table->integer('js_size_kb')->default(0)->after('css_size_kb');
            }
            if (!Schema::hasColumn('theme_version_comparison_stats', 'image_size_kb')) {
                $table->integer('image_size_kb')->default(0)->after('js_size_kb');
            }
        });
    }

    public function down()
    {
        Schema::table('theme_version_comparison_stats', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('theme_version_comparison_stats', 'total_size_diff_kb')) {
                $columnsToDrop[] = 'total_size_diff_kb';
            }
            if (Schema::hasColumn('theme_version_comparison_stats', 'css_size_diff_kb')) {
                $columnsToDrop[] = 'css_size_diff_kb';
            }
            if (Schema::hasColumn('theme_version_comparison_stats', 'js_size_diff_kb')) {
                $columnsToDrop[] = 'js_size_diff_kb';
            }
            if (Schema::hasColumn('theme_version_comparison_stats', 'image_size_diff_kb')) {
                $columnsToDrop[] = 'image_size_diff_kb';
            }
            if (Schema::hasColumn('theme_version_comparison_stats', 'total_size_kb')) {
                $columnsToDrop[] = 'total_size_kb';
            }
            if (Schema::hasColumn('theme_version_comparison_stats', 'css_size_kb')) {
                $columnsToDrop[] = 'css_size_kb';
            }
            if (Schema::hasColumn('theme_version_comparison_stats', 'js_size_kb')) {
                $columnsToDrop[] = 'js_size_kb';
            }
            if (Schema::hasColumn('theme_version_comparison_stats', 'image_size_kb')) {
                $columnsToDrop[] = 'image_size_kb';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
