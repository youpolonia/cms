<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('theme_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('theme_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_protected')->default(false);
            $table->timestamps();
            
            $table->unique(['theme_id', 'name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('theme_branches');
    }
};
