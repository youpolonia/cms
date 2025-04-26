<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('content_version_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_version_id')->constrained('content_versions');
            $table->foreignId('user_id')->constrained('users');
            $table->string('action'); // 'created', 'restored', 'compared'
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('content_version_history');
    }
};