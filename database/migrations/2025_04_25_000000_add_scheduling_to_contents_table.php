<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->timestamp('scheduled_publish_at')->nullable()->after('status');
            $table->timestamp('scheduled_unpublish_at')->nullable()->after('scheduled_publish_at');
            $table->enum('publish_status', ['draft', 'scheduled', 'published', 'unpublished'])
                ->default('draft')
                ->after('scheduled_unpublish_at');
        });
    }

    public function down()
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn([
                'scheduled_publish_at',
                'scheduled_unpublish_at',
                'publish_status'
            ]);
        });
    }
};