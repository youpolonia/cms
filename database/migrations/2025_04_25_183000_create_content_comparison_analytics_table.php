<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('content_comparison_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained();
            $table->foreignId('version1_id')->constrained('content_versions');
            $table->foreignId('version2_id')->constrained('content_versions');
            $table->string('granularity');
            $table->foreignId('user_id')->constrained();
            $table->timestamp('compared_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('content_comparison_analytics');
    }
};