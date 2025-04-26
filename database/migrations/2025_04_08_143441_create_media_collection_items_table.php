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
Schema::create('media_collection_items', function (Blueprint $table) {
    $table->id();
    $table->foreignUuid('media_id')->constrained('media')->cascadeOnDelete();
    $table->foreignId('collection_id')->constrained('media_collections')->cascadeOnDelete();
    $table->integer('position')->default(0);
    $table->text('caption')->nullable();
    $table->timestamps();
    
    $table->unique(['media_id', 'collection_id']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_collection_items');
    }
};
