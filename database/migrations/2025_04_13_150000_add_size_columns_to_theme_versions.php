<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('theme_versions', function (Blueprint $table) {
            $table->integer('total_size_kb')->default(0)->after('files');
            $table->integer('css_size_kb')->default(0)->after('total_size_kb');
            $table->integer('js_size_kb')->default(0)->after('css_size_kb');
            $table->integer('image_size_kb')->default(0)->after('js_size_kb');
        });
    }

    public function down()
    {
        Schema::table('theme_versions', function (Blueprint $table) {
            $table->dropColumn(['total_size_kb', 'css_size_kb', 'js_size_kb', 'image_size_kb']);
        });
    }
};
