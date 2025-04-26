<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('error_resolution_steps', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('handler_class');
            $table->string('handler_method');
            $table->integer('order');
            $table->boolean('is_required')->default(true);
            $table->foreignId('workflow_id')->constrained('error_resolution_workflows');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('error_resolution_steps');
    }
};