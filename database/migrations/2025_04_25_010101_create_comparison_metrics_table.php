<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('comparison_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('comparison_sessions')->cascadeOnDelete();
            $table->foreignId('version_a_id')->constrained('content_versions');
            $table->foreignId('version_b_id')->constrained('content_versions');
            $table->integer('diff_count')->default(0);
            $table->integer('word_count')->default(0);
            $table->integer('character_count')->default(0);
            $table->json('diff_statistics')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('comparison_metrics');
    }
};