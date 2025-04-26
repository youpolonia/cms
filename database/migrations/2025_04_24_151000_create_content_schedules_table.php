<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('content_schedules');
        Schema::create('content_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('publish_at');
            $table->timestamp('unpublish_at')->nullable();
            $table->enum('status', ['pending', 'published', 'unpublished', 'failed']);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['publish_at', 'status']);
            $table->index(['content_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('content_schedules');
    }
};