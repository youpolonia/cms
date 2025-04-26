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
            $table->string('approval_status')->default('pending');
            $table->boolean('is_approved')->default(false);
            $table->timestamp('approved_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_versions', function (Blueprint $table) {
            $table->dropColumn(['approval_status', 'is_approved', 'approved_at']);
        });
    }
};
