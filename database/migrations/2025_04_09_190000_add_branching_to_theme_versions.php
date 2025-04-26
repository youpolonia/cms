<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('theme_versions', function (Blueprint $table) {
            $table->string('branch_name')->nullable()->after('parent_version_id');
            $table->json('tags')->nullable()->after('branch_name');
        });
    }

    public function down()
    {
        Schema::table('theme_versions', function (Blueprint $table) {
            $table->dropColumn(['branch_name', 'tags']);
        });
    }
};
