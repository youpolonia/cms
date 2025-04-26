<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recurring_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_version_id')->constrained('content_versions');
            $table->string('recurrence_pattern')->comment('daily, weekly, monthly');
            $table->json('days_of_week')->nullable()->comment('For weekly patterns');
            $table->json('days_of_month')->nullable()->comment('For monthly patterns');
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('next_run_at');
            $table->dateTime('end_recurrence_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_schedules');
    }
};
