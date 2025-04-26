<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('content_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained()->cascadeOnDelete();
            $table->foreignId('version_id')->constrained('content_versions')->cascadeOnDelete();
            $table->timestamp('publish_at');
            $table->timestamp('unpublish_at')->nullable();
            $table->string('timezone')->default('UTC');
            $table->enum('status', ['pending', 'published', 'unpublished', 'failed'])->default('pending');
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            $table->index('publish_at');
            $table->index('unpublish_at');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('content_schedules');
    }
};