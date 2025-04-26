<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('diff_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('content_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('version1_id')->nullable()->constrained('content_versions');
            $table->foreignId('version2_id')->nullable()->constrained('content_versions');
            $table->string('content1_hash')->nullable()->comment('SHA-256 hash of content1');
            $table->string('content2_hash')->nullable()->comment('SHA-256 hash of content2');
            $table->text('comment');
            $table->json('diff_range')->comment('Line numbers and positions in diff');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('diff_comments');
    }
};