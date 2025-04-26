<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('approval_steps', function (Blueprint $table) {
            // Drop the old foreign key constraint
            $table->dropForeign(['workflow_id']);
            
            // Rename the column to match the table name
            $table->renameColumn('workflow_id', 'approval_workflow_id');
            
            // Add new foreign key constraint
            $table->foreign('approval_workflow_id')
                ->references('id')
                ->on('approval_workflows')
                ->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::table('approval_steps', function (Blueprint $table) {
            $table->dropForeign(['approval_workflow_id']);
            $table->renameColumn('approval_workflow_id', 'workflow_id');
            $table->foreign('workflow_id')
                ->references('id')
                ->on('approval_workflows')
                ->cascadeOnDelete();
        });
    }
};
