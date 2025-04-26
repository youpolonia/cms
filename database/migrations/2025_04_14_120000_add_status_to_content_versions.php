<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('content_versions', function (Blueprint $table) {
            $table->string('status')->default('draft')->after('is_merged');
            $table->string('approval_status')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('content_versions', function (Blueprint $table) {
            $table->dropColumn(['status', 'approval_status']);
        });
    }
};
