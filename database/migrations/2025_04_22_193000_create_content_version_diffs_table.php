<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('content_version_diffs')) {
            Schema::create('content_version_diffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_version_id')->constrained('content_versions');
            $table->foreignId('to_version_id')->constrained('content_versions');
            $table->json('diff_content');
            $table->unsignedInteger('characters_added')->default(0);
            $table->unsignedInteger('characters_removed')->default(0);
            $table->unsignedInteger('words_added')->default(0);
            $table->unsignedInteger('words_removed')->default(0);
            $table->unsignedInteger('lines_added')->default(0);
            $table->unsignedInteger('lines_removed')->default(0);
            $table->json('change_summary');
            $table->timestamps();

            $table->index(['from_version_id', 'to_version_id']);
            });
        } else {
            Schema::table('content_version_diffs', function (Blueprint $table) {
                if (!Schema::hasColumn('content_version_diffs', 'from_version_id')) {
                    $table->foreignId('from_version_id')->constrained('content_versions')->after('id');
                }
                if (!Schema::hasColumn('content_version_diffs', 'to_version_id')) {
                    $table->foreignId('to_version_id')->constrained('content_versions')->after('from_version_id');
                }
                if (!Schema::hasColumn('content_version_diffs', 'diff_content')) {
                    $table->json('diff_content')->after('to_version_id');
                }
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

                // Skip index check since SQLite doesn't support getDoctrineSchemaManager
                // The index is non-critical and can be added manually if needed
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('content_version_diffs');
    }
};