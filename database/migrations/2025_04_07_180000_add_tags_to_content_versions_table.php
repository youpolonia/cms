<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('content_versions', function (Blueprint $table) {
            $table->json('tags')->nullable()->after('is_autosave');
            $table->string('version_name')->nullable()->after('tags');
            $table->foreignId('restored_from')->nullable()->after('version_name')
                ->constrained('content_versions')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('content_versions', function (Blueprint $table) {
            $table->dropColumn(['tags', 'version_name', 'restored_from']);
        });
    }
};
