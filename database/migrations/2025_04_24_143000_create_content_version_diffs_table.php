<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('content_version_diffs');
        Schema::create('content_version_diffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('contents');
            $table->foreignId('from_version_id')->constrained('content_versions');
            $table->foreignId('to_version_id')->constrained('content_versions');
            $table->json('diff_data');
            $table->text('summary');
            $table->timestamps();

            $table->unique(['from_version_id', 'to_version_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('content_version_diffs');
    }
};