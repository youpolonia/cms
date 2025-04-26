<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('content_version_comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('base_version_id')->constrained('content_versions');
            $table->foreignId('compare_version_id')->constrained('content_versions');
            $table->json('metrics');
            $table->json('change_categories');
            $table->string('significance');
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('content_version_comparisons');
    }
};