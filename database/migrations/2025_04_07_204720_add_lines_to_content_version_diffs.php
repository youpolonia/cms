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
            if (!Schema::hasColumn('content_version_diffs', 'lines_added')) {
                $table->integer('lines_added')->default(0)->after('words_removed');
            }
            if (!Schema::hasColumn('content_version_diffs', 'lines_removed')) {
                $table->integer('lines_removed')->default(0)->after('lines_added');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_version_diffs', function (Blueprint $table) {
            $table->dropColumn(['lines_added', 'lines_removed']);
        });
    }
};
