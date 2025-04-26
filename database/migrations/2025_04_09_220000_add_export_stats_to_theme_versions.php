<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('theme_versions', function (Blueprint $table) {
            $table->integer('export_count')->default(0);
            $table->timestamp('last_exported_at')->nullable();
            $table->integer('export_size')->nullable()->comment('Size in bytes');
        });
    }

    public function down()
    {
        Schema::table('theme_versions', function (Blueprint $table) {
            $table->dropColumn(['export_count', 'last_exported_at']);
        });
    }
};
