<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('export_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('export_id')->constrained('analytics_exports');
            $table->string('status'); // completed, failed
            $table->float('processing_time')->nullable(); // in seconds
            $table->integer('file_size')->nullable(); // in bytes
            $table->integer('record_count')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index('export_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('export_metrics');
    }
};