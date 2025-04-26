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
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('version');
            $table->string('author');
            $table->string('screenshot')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('files')->nullable();
            $table->string('marketplace_id')->nullable();
            $table->string('installation_source')->default('manual');
            $table->json('marketplace_metadata')->nullable();
            $table->timestamp('last_checked_for_updates')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
