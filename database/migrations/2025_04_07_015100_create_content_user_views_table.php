<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('content_user_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->integer('time_spent')->default(0);
            $table->float('scroll_depth')->default(0);
            $table->boolean('interacted')->default(false);
            $table->timestamps();
            
            $table->index(['content_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('content_user_views');
    }
};
