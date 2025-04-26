<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('theme_versions', function (Blueprint $table) {
            $table->json('dependencies')->nullable()->after('version_notes');
            $table->json('dependency_changes')->nullable()->after('dependencies');
            $table->string('dependency_status')->nullable()->after('dependency_changes')
                ->comment('added, removed, changed, none');
        });
    }

    public function down()
    {
        Schema::table('theme_versions', function (Blueprint $table) {
            $table->dropColumn(['dependencies', 'dependency_changes', 'dependency_status']);
        });
    }
};
