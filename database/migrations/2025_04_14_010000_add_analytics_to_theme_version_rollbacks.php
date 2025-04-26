<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('theme_version_rollbacks', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained('users')->comment('User who initiated the rollback');
            $table->timestamp('started_at')->nullable()->comment('When the rollback process began');
            $table->integer('file_count')->nullable()->comment('Number of files affected by rollback');
            $table->integer('file_size_kb')->nullable()->comment('Total size of rolled back files in KB');
            $table->json('system_metrics')->nullable()->comment('System load metrics during rollback');
        });
    }

    public function down()
    {
        Schema::table('theme_version_rollbacks', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id',
                'started_at', 
                'file_count',
                'file_size_kb',
                'system_metrics'
            ]);
        });
    }
};
