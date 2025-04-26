<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notification_sound_library', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->integer('duration')->comment('Duration in milliseconds');
            $table->string('category')->default('notification');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add default notification sounds
        DB::table('notification_sound_library')->insert([
            [
                'name' => 'Default Notification',
                'description' => 'Standard notification sound',
                'file_path' => 'default.mp3',
                'duration' => 1500,
                'category' => 'notification',
                'is_default' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Alert',
                'description' => 'Urgent alert sound',
                'file_path' => 'alert.mp3',
                'duration' => 2000,
                'category' => 'alert',
                'is_default' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Success',
                'description' => 'Positive confirmation sound',
                'file_path' => 'success.mp3',
                'duration' => 1200,
                'category' => 'feedback',
                'is_default' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('notification_sound_library');
    }
};