<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('content_versions', function (Blueprint $table) {
            if (!Schema::hasColumn('content_versions', 'is_restored')) {
                $table->boolean('is_restored')->default(false);
            }
            if (!Schema::hasColumn('content_versions', 'restored_at')) {
                $table->timestamp('restored_at')->nullable();
            }
            if (!Schema::hasColumn('content_versions', 'restored_by')) {
                $table->foreignId('restored_by')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn('content_versions', 'restored_from_version_id')) {
                $table->foreignId('restored_from_version_id')->nullable()->constrained('content_versions');
            }
        });
    }

    public function down()
    {
        Schema::table('content_versions', function (Blueprint $table) {
            $table->dropForeign(['restored_by']);
            $table->dropForeign(['restored_from_version_id']);
            $table->dropColumnIfExists('is_restored');
            $table->dropColumnIfExists('restored_at');
            $table->dropColumnIfExists('restored_by');
            $table->dropColumnIfExists('restored_from_version_id');
        });
    }
};