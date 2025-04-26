<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('content_versions', function (Blueprint $table) {
            $table->foreignUuid('branch_id')
                  ->nullable()
                  ->constrained('version_branches')
                  ->after('id');
            $table->string('branch_status')
                  ->nullable()
                  ->after('branch_id');
        });
    }

    public function down(): void
    {
        Schema::table('content_versions', function (Blueprint $table) {
            $table->dropColumn(['branch_id', 'branch_status']);
        });
    }
};