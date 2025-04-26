<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('moderation_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moderation_id')->constrained('moderation_queue');
            $table->foreignId('content_id')->constrained('contents');
            $table->foreignId('moderator_id')->constrained('users');
            $table->string('action');
            $table->string('status');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['moderator_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('moderation_analytics');
    }
};