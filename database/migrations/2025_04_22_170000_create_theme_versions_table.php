<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('theme_versions')) {
            Schema::create('theme_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('theme_id')->constrained()->cascadeOnDelete();
            $table->json('version_data');
            $table->text('description');
            $table->timestamps();
            });
        } else {
            Schema::table('theme_versions', function (Blueprint $table) {
                if (!Schema::hasColumn('theme_versions', 'theme_id')) {
                    $table->foreignId('theme_id')->constrained()->cascadeOnDelete()->after('id');
                }
                if (!Schema::hasColumn('theme_versions', 'version_data')) {
                    $table->json('version_data')->after('theme_id');
                }
                if (!Schema::hasColumn('theme_versions', 'description')) {
                    $table->text('description')->after('version_data');
                }
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('theme_versions');
    }
};