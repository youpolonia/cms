<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('analytics_exports', function (Blueprint $table) {
            if (!Schema::hasColumn('analytics_exports', 'start_date')) {
                $table->date('start_date')->after('user_id');
            }
            if (!Schema::hasColumn('analytics_exports', 'end_date')) {
                $table->date('end_date')->after('start_date');
            }
            if (!Schema::hasColumn('analytics_exports', 'format')) {
                $table->string('format')->default('csv')->after('end_date');
            }
            if (!Schema::hasColumn('analytics_exports', 'file_size')) {
                $table->integer('file_size')->nullable()->after('file_path');
            }
            if (!Schema::hasColumn('analytics_exports', 'error')) {
                $table->text('error')->nullable()->after('status');
            }
            if (!Schema::hasColumn('analytics_exports', 'filters')) {
                $table->json('filters')->nullable()->after('error');
            }
            if (!Schema::hasColumn('analytics_exports', 'is_scheduled')) {
                $table->boolean('is_scheduled')->default(false)->after('filters');
            }
            if (!Schema::hasColumn('analytics_exports', 'schedule_frequency')) {
                $table->string('schedule_frequency')->nullable()->after('is_scheduled');
            }
        });
    }

    public function down()
    {
        Schema::table('analytics_exports', function (Blueprint $table) {
            $table->dropColumn([
                'start_date',
                'end_date',
                'format',
                'file_size',
                'error',
                'filters',
                'is_scheduled',
                'schedule_frequency'
            ]);
        });
    }
};