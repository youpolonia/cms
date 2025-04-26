<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('theme_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('theme_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1-5 stars
            $table->text('review')->nullable();
            $table->timestamps();
            
            $table->unique(['theme_id', 'user_id']); // One rating per user per theme
        });
    }

    public function down()
    {
        Schema::dropIfExists('theme_ratings');
    }
};
