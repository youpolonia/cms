<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('version_comparison_stats')) {
            Schema::create('version_comparison_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_a_id')->constrained('content_versions');
            $table->foreignId('version_b_id')->constrained('content_versions');
            $table->foreignId('content_id')->constrained('contents');
            $table->foreignId('user_id')->nullable()->constrained('users');
            
            $table->integer('similarity_percentage');
            $table->integer('lines_added');
            $table->integer('lines_removed');
            $table->integer('lines_unchanged');
            $table->integer('words_added');
            $table->integer('words_removed');
            $table->integer('words_unchanged');
            
            $table->json('frequent_changes')->nullable();
            $table->json('change_distribution')->nullable();
            
            $table->timestamp('compared_at')->useCurrent();
            
            $table->index(['version_a_id', 'version_b_id']);
            $table->index('content_id');
            $table->index('user_id');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('version_comparison_stats')) {
            Schema::dropIfExists('version_comparison_stats');
        }
    }
};