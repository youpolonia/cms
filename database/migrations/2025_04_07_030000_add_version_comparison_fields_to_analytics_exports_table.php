<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('analytics_exports', function (Blueprint $table) {
            $table->foreignId('content_id')->nullable()->constrained('contents')->cascadeOnDelete();
            $table->string('format')->default('csv');
            $table->string('type')->default('general');
            $table->unsignedTinyInteger('progress')->default(0);
            $table->json('anonymization_options')->nullable();
            
            $table->index(['content_id', 'type']);
            $table->index(['status', 'expires_at']);
        });
    }

    public function down()
    {
        Schema::table('analytics_exports', function (Blueprint $table) {
            $table->dropForeign(['content_id']);
            $table->dropColumn(['content_id', 'format', 'type', 'progress', 'anonymization_options']);
            $table->dropIndex(['content_id_type_index']);
            $table->dropIndex(['status_expires_at_index']);
        });
    }
};