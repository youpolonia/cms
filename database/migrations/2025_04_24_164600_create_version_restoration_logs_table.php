<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('version_restoration_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_version_id')->constrained('content_versions');
            $table->foreignId('user_id')->constrained('users');
            $table->string('ip_address');
            $table->text('reason');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('version_restoration_logs');
    }
};