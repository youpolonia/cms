<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('analytics_exports', function (Blueprint $table) {
            $table->integer('extension_count')->default(0);
            $table->dateTime('last_extended_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('analytics_exports', function (Blueprint $table) {
            $table->dropColumn(['extension_count', 'last_extended_at']);
        });
    }
};