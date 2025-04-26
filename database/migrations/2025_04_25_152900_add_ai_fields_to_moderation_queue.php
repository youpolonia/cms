<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('moderation_queue', function (Blueprint $table) {
            $table->boolean('is_ai_generated')->default(false)->after('content_id');
            $table->json('ai_generation_metadata')->nullable()->after('is_ai_generated');
            $table->json('openai_moderation_results')->nullable()->after('ai_generation_metadata');
            $table->string('moderation_policy')->default('standard')->after('openai_moderation_results');
            $table->boolean('requires_human_review')->default(false)->after('moderation_policy');
        });
    }

    public function down()
    {
        Schema::table('moderation_queue', function (Blueprint $table) {
            $table->dropColumn([
                'is_ai_generated',
                'ai_generation_metadata',
                'openai_moderation_results',
                'moderation_policy',
                'requires_human_review'
            ]);
        });
    }
};