<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_comparison_behavior', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('total_sessions')->default(0);
            $table->integer('average_versions_per_session')->default(0);
            $table->integer('average_session_duration')->default(0);
            $table->json('frequent_comparison_times')->nullable();
            $table->json('common_comparison_patterns')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_comparison_behavior');
    }
};