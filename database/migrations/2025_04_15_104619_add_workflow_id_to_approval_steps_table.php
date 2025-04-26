<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('approval_steps', function (Blueprint $table) {
            $table->foreignId('workflow_id')
                ->constrained('approval_workflows')
                ->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::table('approval_steps', function (Blueprint $table) {
            $table->dropForeign(['workflow_id']);
            $table->dropColumn('workflow_id');
        });
    }
};
