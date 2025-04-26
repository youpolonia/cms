<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('moderation_queue', function (Blueprint $table) {
            $table->id();
            $table->string('content_type')->index(); // article, comment, media, etc
            $table->foreignId('content_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending')->index(); // pending, approved, rejected
            $table->text('rejection_reason')->nullable();
            $table->json('moderation_metadata')->nullable(); // AI analysis, flags, etc
            $table->json('moderation_result')->nullable();
            $table->foreignId('moderator_id')->nullable()->constrained('users');
            $table->timestamp('moderated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('moderation_queue');
    }
};
