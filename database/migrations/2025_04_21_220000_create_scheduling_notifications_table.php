<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('scheduling_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('schedule_id')->constrained('content_schedules')->onDelete('cascade');
            $table->string('type'); // upcoming, conflict, completed, changed
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('scheduling_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('email_upcoming')->default(true);
            $table->boolean('email_conflicts')->default(true);
            $table->boolean('email_completed')->default(false);
            $table->boolean('email_changes')->default(true);
            $table->boolean('in_app_upcoming')->default(true);
            $table->boolean('in_app_conflicts')->default(true);
            $table->boolean('in_app_completed')->default(true);
            $table->boolean('in_app_changes')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('scheduling_notification_preferences');
        Schema::dropIfExists('scheduling_notifications');
    }
};