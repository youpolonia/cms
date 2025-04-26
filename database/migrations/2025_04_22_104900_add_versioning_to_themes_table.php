<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->string('current_version')->default('1.0.0');
            $table->json('version_history')->nullable();
            $table->string('update_available_version')->nullable();
            $table->string('update_available_url')->nullable();
            $table->timestamp('last_checked_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->dropColumn([
                'current_version',
                'version_history', 
                'update_available_version',
                'update_available_url',
                'last_checked_at'
            ]);
        });
    }
};