<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('version_comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('contents');
            $table->foreignId('base_version_id')->constrained('content_versions');
            $table->foreignId('compare_version_id')->constrained('content_versions');
            $table->json('diff_results');
            $table->text('summary')->nullable();
            $table->foreignId('compared_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('version_comparisons');
    }
};