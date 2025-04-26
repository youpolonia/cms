<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('content_version_diffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_version_id')->constrained('content_versions');
            $table->foreignId('to_version_id')->constrained('content_versions');
            $table->json('diff_content');
            $table->integer('characters_added')->default(0);
            $table->integer('characters_removed')->default(0);
            $table->integer('words_added')->default(0);
            $table->integer('words_removed')->default(0);
            $table->integer('lines_added')->default(0);
            $table->integer('lines_removed')->default(0);
            $table->json('change_summary')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('content_version_diffs');
    }
};
