<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_suggestion_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('suggestion_id')->constrained('content_suggestions')->cascadeOnDelete();
            $table->string('interaction_type'); // 'click', 'dismiss', 'save', etc.
            $table->json('metadata')->nullable(); // Additional interaction data
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_suggestion_interactions');
    }
};