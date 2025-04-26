<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('block_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')->constrained('blocks');
            $table->foreignId('user_id')->constrained('users');
            $table->json('content');
            $table->text('changes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('block_versions');
    }
};