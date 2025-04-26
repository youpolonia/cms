<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('theme_versions', function (Blueprint $table) {
            $table->foreignId('parent_version_id')
                ->nullable()
                ->constrained('theme_versions')
                ->nullOnDelete();
            $table->json('file_changes')->nullable();
            $table->json('diff_data')->nullable();
            $table->boolean('is_rollback')->default(false);
        });
    }

    public function down()
    {
        Schema::table('theme_versions', function (Blueprint $table) {
            $table->dropForeign(['parent_version_id']);
            $table->dropColumn(['parent_version_id', 'file_changes', 'diff_data', 'is_rollback']);
        });
    }
};
