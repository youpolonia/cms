<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('theme_approval_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('theme_version_id')->constrained('theme_versions');
            $table->foreignId('approval_step_id')->constrained('theme_approval_steps');
            
            // Approval metrics
            $table->integer('approvals_count')->default(0);
            $table->integer('rejections_count')->default(0);
            $table->integer('required_approvals');
            $table->string('approval_logic')->default('any'); // 'any' or 'all'
            
            // Time metrics
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('time_taken_seconds')->nullable(); // in seconds
            $table->boolean('met_deadline')->nullable();
            
            // Parallel approval progress
            $table->decimal('completion_percentage', 5, 2)->default(0);
            $table->json('approvers_progress')->nullable(); // {user_id: status, ...}
            
            // Step requirements tracking
            $table->json('requirements_met')->nullable();
            
            $table->timestamps();
            
            $table->index(['theme_version_id', 'approval_step_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('theme_approval_statistics');
    }
};
