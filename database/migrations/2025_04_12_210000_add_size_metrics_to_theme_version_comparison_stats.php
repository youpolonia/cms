<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('theme_version_comparison_stats', function (Blueprint $table) {
            $table->unsignedInteger('original_size_kb')->nullable()->after('complexity_score');
            $table->unsignedInteger('new_size_kb')->nullable()->after('original_size_kb');
            $table->integer('size_change_kb')->nullable()->after('new_size_kb');
            $table->decimal('size_change_percent', 5, 2)->nullable()->after('size_change_kb');
            $table->json('file_size_changes')->nullable()->after('size_change_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('theme_version_comparison_stats', function (Blueprint $table) {
            $table->dropColumn([
                'original_size_kb',
                'new_size_kb',
                'size_change_kb',
                'size_change_percent',
                'file_size_changes'
            ]);
        });
    }
};
