<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('content_versions', function (Blueprint $table) {
            // Only add fields that are confirmed missing
            if (!Schema::hasColumn('content_versions', 'branch_name')) {
                $table->string('branch_name')->nullable();
            }
            if (!Schema::hasColumn('content_versions', 'is_merged')) {
                $table->boolean('is_merged')->default(false);
            }
            if (!Schema::hasColumn('content_versions', 'merged_at')) {
                $table->dateTime('merged_at')->nullable();
            }
            if (!Schema::hasColumn('content_versions', 'tags')) {
                $table->text('tags')->nullable();
            }
            if (!Schema::hasColumn('content_versions', 'comparison_metrics')) {
                $table->text('comparison_metrics')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('content_versions', function (Blueprint $table) {
            $columns = [];
            
            if (Schema::hasColumn('content_versions', 'branch_name')) {
                $columns[] = 'branch_name';
            }
            if (Schema::hasColumn('content_versions', 'is_merged')) {
                $columns[] = 'is_merged';
            }
            if (Schema::hasColumn('content_versions', 'merged_at')) {
                $columns[] = 'merged_at';
            }
            if (Schema::hasColumn('content_versions', 'tags')) {
                $columns[] = 'tags';
            }
            if (Schema::hasColumn('content_versions', 'comparison_metrics')) {
                $columns[] = 'comparison_metrics';
            }
            
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};