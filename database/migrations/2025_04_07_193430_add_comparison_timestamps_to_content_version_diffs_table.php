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
        Schema::table('content_version_diffs', function (Blueprint $table) {
            $table->timestamp('compared_at')->nullable()->after('diff_summary');
            $table->index('compared_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_version_diffs', function (Blueprint $table) {
            $table->dropIndex(['compared_at']);
            $table->dropColumn('compared_at');
        });
    }
};
