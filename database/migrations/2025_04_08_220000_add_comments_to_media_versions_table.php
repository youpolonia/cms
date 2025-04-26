<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('media_versions', function (Blueprint $table) {
            $table->text('comment')->nullable()->after('metadata');
            $table->foreignId('created_by')->constrained('users')->after('comment');
        });
    }

    public function down()
    {
        Schema::table('media_versions', function (Blueprint $table) {
            $table->dropColumn('comment');
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
    }
};
