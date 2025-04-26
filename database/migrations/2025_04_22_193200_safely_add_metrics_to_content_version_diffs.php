<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('content_version_diffs', function (Blueprint $table) {
            if (!Schema::hasColumn('content_version_diffs', 'characters_added')) {
                $table->unsignedInteger('characters_added')->default(0)->after('diff_content');
            }
            if (!Schema::hasColumn('content_version_diffs', 'characters_removed')) {
                $table->unsignedInteger('characters_removed')->default(0)->after('characters_added');
            }
            if (!Schema::hasColumn('content_version_diffs', 'words_added')) {
                $table->unsignedInteger('words_added')->default(0)->after('characters_removed');
            }
            if (!Schema::hasColumn('content_version_diffs', 'words_removed')) {
                $table->unsignedInteger('words_removed')->default(0)->after('words_added');
            }
            if (!Schema::hasColumn('content_version_diffs', 'lines_added')) {
                $table->unsignedInteger('lines_added')->default(0)->after('words_removed');
            }
            if (!Schema::hasColumn('content_version_diffs', 'lines_removed')) {
                $table->unsignedInteger('lines_removed')->default(0)->after('lines_added');
            }
            if (!Schema::hasColumn('content_version_diffs', 'change_summary')) {
                $table->json('change_summary')->after('lines_removed');
            }
        });
    }

    public function down()
    {
        // No down migration since we don't know which columns were actually added
    }
};