<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('theme_version_comparison_stats', function (Blueprint $table) {
            $table->json('security_issues')->nullable()->after('performance_impact');
            $table->unsignedInteger('cyclomatic_complexity_before')->default(0)->after('security_issues');
            $table->unsignedInteger('cyclomatic_complexity_after')->default(0)->after('cyclomatic_complexity_before');
            $table->unsignedInteger('cognitive_complexity_before')->default(0)->after('cyclomatic_complexity_after');
            $table->unsignedInteger('cognitive_complexity_after')->default(0)->after('cognitive_complexity_before');
            $table->json('file_type_metrics')->nullable()->after('cognitive_complexity_after');
        });
    }

    public function down()
    {
        Schema::table('theme_version_comparison_stats', function (Blueprint $table) {
            $table->dropColumn([
                'coverage_change',
                'performance_impact',
                'security_issues',
                'cyclomatic_complexity_before',
                'cyclomatic_complexity_after',
                'cognitive_complexity_before',
                'cognitive_complexity_after',
                'file_type_metrics'
            ]);
        });
    }
};
