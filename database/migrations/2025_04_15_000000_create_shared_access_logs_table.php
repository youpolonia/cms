<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shared_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_comparison_stat_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('referrer')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('accessed_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shared_access_logs');
    }
};
