<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('theme_version_comparison_stats', function (Blueprint $table) {
            // File size metrics
            $table->bigInteger('total_size_before')->nullable()->after('lines_removed');
            $table->bigInteger('total_size_after')->nullable()->after('total_size_before');
            $table->bigInteger('size_change')->nullable()->after('total_size_after');
            
            // Performance metrics
            $table->decimal('load_time_before', 8, 3)->nullable()->after('performance_impact');
            $table->decimal('load_time_after', 8, 3)->nullable()->after('load_time_before');
            $table->decimal('memory_usage_before', 8, 2)->nullable()->after('load_time_after');
            $table->decimal('memory_usage_after', 8, 2)->nullable()->after('memory_usage_before');
            
            // File type breakdown
            $table->json('file_type_changes')->nullable()->after('comparison_data');
        });
    }

    public function down()
    {
        Schema::table('theme_version_comparison_stats', function (Blueprint $table) {
            $table->dropColumn([
                'total_size_before',
                'total_size_after',
                'size_change',
                'load_time_before',
                'load_time_after',
                'memory_usage_before',
                'memory_usage_after',
                'file_type_changes'
            ]);
        });
    }
};
