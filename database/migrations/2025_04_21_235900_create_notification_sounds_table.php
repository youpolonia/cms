<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('notification_sounds')) {
            Schema::create('notification_sounds', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('file_path');
                $table->unsignedInteger('duration')->default(0);
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });

            // Add default notification sounds
            DB::table('notification_sounds')->insert([
                ['name' => 'Default Chime', 'file_path' => 'sounds/default.mp3', 'duration' => 2000, 'is_default' => true],
                ['name' => 'Soft Ping', 'file_path' => 'sounds/soft_ping.mp3', 'duration' => 1500, 'is_default' => false],
                ['name' => 'Alert Tone', 'file_path' => 'sounds/alert.mp3', 'duration' => 3000, 'is_default' => false]
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('notification_sounds');
    }
};