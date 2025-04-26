<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('theme_versions', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('version');
            $table->json('files')->nullable()->after('is_active');
            $table->foreignId('user_id')->nullable()->constrained()->after('files');
        });
    }

    public function down()
    {
        Schema::table('theme_versions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['notes', 'files', 'user_id']);
        });
    }
};
