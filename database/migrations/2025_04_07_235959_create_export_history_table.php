<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('export_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('scheduled_exports')->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('report_templates');
            $table->string('status'); // success, failed, partial
            $table->string('file_path')->nullable();
            $table->integer('file_size')->nullable(); // in bytes
            $table->integer('recipient_count');
            $table->text('error_log')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('export_history');
    }
};