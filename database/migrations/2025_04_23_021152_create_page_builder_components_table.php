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
        Schema::create('page_builder_components', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // text, image, video, etc.
            $table->json('content')->nullable(); // Component content data
            $table->foreignId('page_id')->constrained('contents')->onDelete('cascade');
            $table->integer('position')->default(0);
            $table->json('styles')->nullable(); // CSS styles
            $table->json('settings')->nullable(); // Component settings
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_builder_components');
    }
};
