<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create tags table
        Schema::create('export_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('#3b82f6');
            $table->timestamps();
        });

        // Create pivot table
        Schema::create('analytics_export_tags', function (Blueprint $table) {
            $table->foreignId('export_id')->constrained('analytics_exports')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('export_tags')->cascadeOnDelete();
            $table->timestamps();
            
            $table->primary(['export_id', 'tag_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('analytics_export_tags');
        Schema::dropIfExists('export_tags');
    }
};