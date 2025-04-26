<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('version_comparisons', function (Blueprint $table) {
            $table->json('metrics')->nullable()->after('summary');
        });
    }

    public function down()
    {
        Schema::table('version_comparisons', function (Blueprint $table) {
            $table->dropColumn('metrics');
        });
    }
};
