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
        Schema::table('content_versions', function (Blueprint $table) {
            $table->boolean('is_restored')->default(false);
            $table->timestamp('restored_at')->nullable();
            $table->foreignId('restored_by')->nullable()->constrained('users');
            $table->foreignId('restored_from_version_id')->nullable()->constrained('content_versions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_versions', function (Blueprint $table) {
            $table->dropColumn(['is_restored', 'restored_at', 'restored_by', 'restored_from_version_id']);
        });
    }
};
