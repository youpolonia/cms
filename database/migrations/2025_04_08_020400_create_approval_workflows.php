<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('approval_workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('approval_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('order');
            $table->string('name');
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_required')->default(true);
            $table->timestamps();
        });

        Schema::table('contents', function (Blueprint $table) {
            $table->string('approval_status')->default('draft')
                ->after('is_published');
            $table->foreignId('approval_workflow_id')
                ->nullable()
                ->after('approval_status')
                ->constrained()
                ->onDelete('set null');
            $table->foreignId('current_approval_step_id')
                ->nullable()
                ->after('approval_workflow_id')
                ->constrained('approval_steps')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('contents', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['approval_workflow_id']);
            $table->dropForeign(['current_approval_step_id']);

            // Conditionally drop columns if they exist
            if (Schema::hasColumn('contents', 'approval_status')) {
                $table->dropColumn('approval_status');
            }
            if (Schema::hasColumn('contents', 'approval_workflow_id')) {
                $table->dropColumn('approval_workflow_id');
            }
            if (Schema::hasColumn('contents', 'current_approval_step_id')) {
                $table->dropColumn('current_approval_step_id');
            }
        });

        Schema::dropIfExists('approval_steps');
        Schema::dropIfExists('approval_workflows');
    }
};
