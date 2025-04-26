<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('theme_version_approvals', function (Blueprint $table) {
            $table->integer('completed_steps')->default(0);
            $table->integer('total_steps')->default(0);
            $table->decimal('progress_percentage', 5, 2)->default(0);
        });
    }

    public function down()
    {
        Schema::table('theme_version_approvals', function (Blueprint $table) {
            $table->dropColumn(['completed_steps', 'total_steps', 'progress_percentage']);
        });
    }
};
