<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notification_sounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('notification_type');
            $table->string('sound_file')->nullable();
            $table->boolean('enabled')->default(true);
            $table->float('volume')->default(0.8);
            $table->timestamps();

            $table->unique(['user_id', 'notification_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('notification_sounds');
    }
};