<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('ai_daily_usage')->default(0);
            $table->unsignedInteger('ai_daily_limit')->default(10000);
            $table->unsignedInteger('ai_monthly_usage')->default(0);
            $table->unsignedInteger('ai_monthly_limit')->default(300000);
            $table->unsignedInteger('ai_total_usage')->default(0);
            $table->decimal('ai_total_cost', 10, 4)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'ai_daily_usage',
                'ai_daily_limit',
                'ai_monthly_usage',
                'ai_monthly_limit',
                'ai_total_usage',
                'ai_total_cost'
            ]);
        });
    }
};