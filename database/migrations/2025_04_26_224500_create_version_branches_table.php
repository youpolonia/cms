<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('version_branches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->foreignUuid('root_version_id')->constrained('content_versions');
            $table->foreignUuid('current_head_id')->constrained('content_versions');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('version_branches');
    }
};