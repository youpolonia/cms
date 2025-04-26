<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('version_comparison_stats', function (Blueprint $table) {
            // Remove old columns if they exist
            if (Schema::hasColumn('version_comparison_stats', 'character_changes')) {
                $table->dropColumn(['character_changes', 'word_changes', 'line_changes', 'comparison_count']);
            }
            
            // Add new columns
            $table->unsignedInteger('changes_count')->default(0)->after('to_version_id');
            $table->unsignedInteger('conflicts_count')->default(0)->after('changes_count');
            $table->unsignedInteger('resolutions_count')->default(0)->after('conflicts_count');
            $table->json('resolution_stats')->nullable()->after('resolutions_count');
        });
    }

    public function down()
    {
        Schema::table('version_comparison_stats', function (Blueprint $table) {
            // Re-add old columns
            $table->unsignedInteger('character_changes')->default(0);
            $table->unsignedInteger('word_changes')->default(0);
            $table->unsignedInteger('line_changes')->default(0);
            $table->unsignedInteger('comparison_count')->default(0);
            
            // Remove new columns
            $table->dropColumn([
                'changes_count',
                'conflicts_count',
                'resolutions_count',
                'resolution_stats'
            ]);
        });
    }
};
