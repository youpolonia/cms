<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('operation_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'restoration', 'export', 'bulk_action'
            $table->string('status'); // 'pending', 'completed', 'failed'
            $table->text('message');
            $table->json('metadata')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('operation_notifications');
    }
};