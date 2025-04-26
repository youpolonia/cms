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
Schema::create('media', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('filename');
    $table->string('path');
    $table->json('metadata')->nullable();
    $table->foreignId('user_id')->constrained();
    $table->timestamps();
    $table->softDeletes();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
