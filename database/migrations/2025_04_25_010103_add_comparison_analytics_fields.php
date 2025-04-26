<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('contents', function (Blueprint $table) {
            if (!Schema::hasColumn('contents', 'comparison_count')) {
                $table->integer('comparison_count')->default(0);
            }
            if (!Schema::hasColumn('contents', 'last_comparison_at')) {
                $table->timestamp('last_comparison_at')->nullable();
            }
        });

        Schema::table('content_versions', function (Blueprint $table) {
            if (!Schema::hasColumn('content_versions', 'times_compared')) {
                $table->integer('times_compared')->default(0);
            }
        });
    }

    public function down()
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn(['comparison_count', 'last_comparison_at']);
        });

        Schema::table('content_versions', function (Blueprint $table) {
            $table->dropColumn('times_compared');
        });
    }
};