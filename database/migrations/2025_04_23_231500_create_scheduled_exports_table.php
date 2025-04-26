<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('scheduled_exports')) {
            Schema::create('scheduled_exports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('frequency'); // daily, weekly, monthly
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->dateTime('last_run_at')->nullable();
            $table->dateTime('next_run_at');
            $table->string('status')->default('active'); // active, paused, completed
            $table->foreignId('user_id')->constrained();
            $table->boolean('anonymize')->default(false);
            $table->json('anonymization_options')->nullable();
            $table->json('export_params')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('next_run_at');
            $table->index('status');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('scheduled_exports')) {
            Schema::dropIfExists('scheduled_exports');
        }
    }
};