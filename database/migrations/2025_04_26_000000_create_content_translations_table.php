<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('content_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained()->cascadeOnDelete();
            $table->string('language_code', 2);
            $table->json('translated_content');
            $table->timestamps();

            $table->unique(['content_id', 'language_code']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('content_translations');
    }
};