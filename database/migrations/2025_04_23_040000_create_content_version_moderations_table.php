<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('content_version_moderations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_version_id')->constrained();
            $table->foreignId('moderator_id')->constrained('users');
            $table->enum('action', ['approve', 'reject', 'request_changes']);
            $table->text('notes')->nullable();
            $table->json('changes_requested')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('content_version_moderations');
    }
};