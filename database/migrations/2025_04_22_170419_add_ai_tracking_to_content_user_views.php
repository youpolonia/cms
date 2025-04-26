<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('content_user_views', function (Blueprint $table) {
            $table->boolean('used_ai_generation')->default(false);
            $table->boolean('used_block_suggestions')->default(false);
            $table->string('generation_prompt_type')->nullable();
            $table->integer('ai_generated_blocks')->default(0);
            $table->integer('suggested_blocks_used')->default(0);
        });
    }

    public function down()
    {
        Schema::table('content_user_views', function (Blueprint $table) {
            $table->dropColumn([
                'used_ai_generation',
                'used_block_suggestions', 
                'generation_prompt_type',
                'ai_generated_blocks',
                'suggested_blocks_used'
            ]);
        });
    }
};
