<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained()->cascadeOnDelete();
            $table->string('suggestion_type'); // e.g. 'similar', 'complementary', 'trending'
            $table->json('suggested_content_ids'); // Array of content IDs
            $table->json('metadata')->nullable(); // Additional suggestion data
            $table->float('relevance_score')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_suggestions');
    }
};