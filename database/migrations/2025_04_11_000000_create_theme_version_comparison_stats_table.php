<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('theme_version_comparison_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('theme_version_id')->constrained('theme_versions');
            $table->foreignId('compared_version_id')->constrained('theme_versions');
            
            // File changes
            $table->integer('files_added')->default(0);
            $table->integer('files_removed')->default(0);
            $table->integer('files_modified')->default(0);
            
            // Code changes
            $table->integer('lines_added')->default(0);
            $table->integer('lines_removed')->default(0);
            
            // Quality metrics
            $table->decimal('quality_score', 5, 2)->default(0);
            $table->integer('complexity_change')->default(0);
            $table->decimal('coverage_change', 5, 2)->default(0);
            
            // Performance impact
            $table->decimal('performance_impact', 5, 2)->default(0);
            
            // Additional comparison data
            $table->json('comparison_data')->nullable();
            
            $table->timestamps();
            
            $table->index(['theme_version_id', 'compared_version_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('theme_version_comparison_stats');
    }
};
