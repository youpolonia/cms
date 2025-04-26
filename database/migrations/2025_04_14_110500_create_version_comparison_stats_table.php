<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('version_comparison_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('version_id')->constrained('content_versions')->cascadeOnDelete();
            $table->foreignId('compared_version_id')->constrained('content_versions')->cascadeOnDelete();
            
            // Session metrics
            $table->integer('duration_seconds');
            $table->integer('changes_viewed');
            $table->integer('toggles_count');
            $table->integer('navigations_count');
            
            // View preferences
            $table->integer('word_level_view_time');
            $table->integer('line_level_view_time');
            
            // Comparison details
            $table->integer('lines_added');
            $table->integer('lines_removed');
            $table->integer('lines_modified');
            $table->integer('words_added');
            $table->integer('words_removed');
            $table->integer('words_modified');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('version_comparison_stats');
    }
};
