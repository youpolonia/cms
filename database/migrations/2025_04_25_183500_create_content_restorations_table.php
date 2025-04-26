<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('content_restorations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained();
            $table->foreignId('version_id')->constrained('content_versions');
            $table->foreignId('restored_by')->constrained('users');
            $table->timestamp('restored_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('content_restorations');
    }
};