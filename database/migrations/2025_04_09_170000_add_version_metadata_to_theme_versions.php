<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('theme_versions', function (Blueprint $table) {
            $table->foreignId('creator_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('status')
                ->default('draft')
                ->comment('draft, published, archived');
            $table->unsignedTinyInteger('major_version')->default(1);
            $table->unsignedTinyInteger('minor_version')->default(0);
            $table->unsignedTinyInteger('patch_version')->default(0);
            $table->text('description')->nullable();
        });
    }

    public function down()
    {
        Schema::table('theme_versions', function (Blueprint $table) {
            $table->dropForeign(['creator_id']);
            $table->dropColumn([
                'creator_id',
                'status',
                'major_version',
                'minor_version',
                'patch_version',
                'description'
            ]);
        });
    }
};
