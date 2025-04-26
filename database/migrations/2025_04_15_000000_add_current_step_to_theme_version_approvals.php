<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('theme_version_approvals', function (Blueprint $table) {
            $table->foreignId('current_step_id')
                ->nullable()
                ->constrained('theme_approval_steps')
                ->after('workflow_id');
        });
    }

    public function down()
    {
        Schema::table('theme_version_approvals', function (Blueprint $table) {
            $table->dropForeign(['current_step_id']);
            $table->dropColumn('current_step_id');
        });
    }
};
