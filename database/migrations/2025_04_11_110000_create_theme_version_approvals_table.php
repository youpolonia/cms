<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('theme_version_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained('theme_versions');
            $table->foreignId('step_id')->constrained('theme_approval_steps');
            $table->foreignId('user_id')->constrained('users');
            $table->string('status')->default('pending');
            $table->text('comments')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('theme_version_approvals');
    }
};
