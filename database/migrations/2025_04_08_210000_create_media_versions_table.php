<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('media_id')->constrained('media')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->integer('version_number');
            $table->string('filename');
            $table->string('path');
            $table->json('metadata')->nullable();
            $table->text('changes')->nullable();
            $table->timestamps();
        });

        Schema::table('media', function (Blueprint $table) {
            $table->integer('current_version')->default(1);
            $table->integer('version_count')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_versions');
        
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn(['current_version', 'version_count']);
        });
    }
};
