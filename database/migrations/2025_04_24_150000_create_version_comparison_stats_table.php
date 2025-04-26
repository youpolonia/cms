<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('version_comparison_stats');
        Schema::create('version_comparison_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_version_id')->constrained('content_versions');
            $table->foreignId('compared_version_id')->constrained('content_versions');
            $table->json('diff_stats')->comment('JSON containing line/chars changed, additions, deletions');
            $table->float('similarity_score');
            $table->text('summary')->nullable();
            $table->timestamps();
            
            $table->index(['content_version_id', 'compared_version_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('version_comparison_stats');
    }
};