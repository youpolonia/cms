<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('theme_version_rollbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained('theme_versions');
            $table->foreignId('rollback_to_version_id')->constrained('theme_versions');
            $table->string('status')->default('pending')->comment('pending, processing, completed, failed');
            $table->text('notes')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('theme_version_rollbacks');
    }
};
