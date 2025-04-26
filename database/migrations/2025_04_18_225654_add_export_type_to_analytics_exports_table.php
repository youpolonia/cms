<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('analytics_exports', function (Blueprint $table) {
            $table->string('export_type')->default('approval')->after('status');
            if (!Schema::hasColumn('analytics_exports', 'content_id')) {
                $table->foreignId('content_id')
                    ->nullable()
                    ->constrained('contents')
                    ->nullOnDelete()
                    ->after('export_type');
            }
        });
    }

    public function down()
    {
        Schema::table('analytics_exports', function (Blueprint $table) {
            $table->dropForeign(['content_id']);
            $table->dropColumn(['export_type', 'content_id']);
        });
    }
};
