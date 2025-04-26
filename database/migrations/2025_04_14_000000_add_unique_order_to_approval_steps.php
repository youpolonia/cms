<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('approval_steps', function (Blueprint $table) {
            $table->unique(['workflow_id', 'order']);
        });
    }

    public function down()
    {
        Schema::table('approval_steps', function (Blueprint $table) {
            $table->dropUnique(['workflow_id', 'order']);
        });
    }
};
