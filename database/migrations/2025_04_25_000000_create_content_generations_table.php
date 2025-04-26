<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('content_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('prompt');
            $table->longText('content');
            $table->json('parameters');
            $table->string('model')->default('gpt-3.5-turbo');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('content_generations');
    }
};