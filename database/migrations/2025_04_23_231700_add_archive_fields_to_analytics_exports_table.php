<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('analytics_exports', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable();
            $table->string('archive_path')->nullable();
            $table->string('original_file_path')->nullable();
        });
    }

    public function down()
    {
        Schema::table('analytics_exports', function (Blueprint $table) {
            $table->dropColumn(['archived_at', 'archive_path', 'original_file_path']);
        });
    }
};