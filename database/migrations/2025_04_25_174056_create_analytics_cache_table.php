<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('analytics_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version1_id')->constrained('content_versions');
            $table->foreignId('version2_id')->constrained('content_versions');
            $table->integer('change_count')->unsigned();
            $table->integer('additions')->unsigned();
            $table->integer('deletions')->unsigned();
            $table->integer('changed_lines')->unsigned();
            $table->float('similarity', 5, 2);
            $table->integer('version1_views')->unsigned();
            $table->integer('version2_views')->unsigned();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->unique(['version1_id', 'version2_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_cache');
    }
};
