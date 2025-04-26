<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('contents', function (Blueprint $table) {
            if (!Schema::hasColumn('contents', 'publish_at')) {
                $table->dateTime('publish_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('contents', 'unpublish_at')) {
                $table->dateTime('unpublish_at')->nullable()->after('publish_at');
            }
            if (!Schema::hasColumn('contents', 'is_recurring')) {
                $table->boolean('is_recurring')->default(false)->after('unpublish_at');
            }
            if (!Schema::hasColumn('contents', 'recurrence_pattern')) {
                $table->string('recurrence_pattern')->nullable()->after('is_recurring');
            }
        });
    }

    public function down()
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn([
                'publish_at',
                'unpublish_at', 
                'is_recurring',
                'recurrence_pattern'
            ]);
        });
    }
};
