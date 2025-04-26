<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('notification_preferences')) {
            Schema::create('notification_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('type');
                $table->boolean('enabled')->default(true);
                $table->foreignId('sound_id')->nullable()->constrained('notification_sound_library');
                $table->unsignedTinyInteger('volume')->default(80);
                $table->timestamps();

                $table->unique(['user_id', 'type']);
            });
        } else {
            Schema::table('notification_preferences', function (Blueprint $table) {
                if (!Schema::hasColumn('notification_preferences', 'sound_id')) {
                    $table->foreignId('sound_id')->nullable()->constrained('notification_sounds')->after('enabled');
                }
                if (!Schema::hasColumn('notification_preferences', 'volume')) {
                    $table->unsignedTinyInteger('volume')->default(80)->after('sound_id');
                }
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('notification_preferences');
    }
};