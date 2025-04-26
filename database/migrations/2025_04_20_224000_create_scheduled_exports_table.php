<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('scheduled_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('report_templates');
            $table->string('frequency'); // daily, weekly, monthly
            $table->time('time');
            $table->string('format'); // pdf, csv, excel
            $table->json('recipients'); // array of email addresses
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at');
            $table->timestamps();
            $table->softDeletes();

            $table->index('template_id');
            $table->index('next_run_at');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('scheduled_exports');
    }
};