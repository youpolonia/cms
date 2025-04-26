<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('theme_approval_workflows', function (Blueprint $table) {
            $table->integer('step_timeout_hours')->nullable()
                ->comment('Number of hours before step times out');
            $table->json('escalation_roles')->nullable()
                ->comment('Roles to escalate to when step times out');
            $table->boolean('auto_approve_after_timeout')->default(false)
                ->comment('Whether to auto-approve if step times out');

            $table->index('step_timeout_hours');
            $table->index('auto_approve_after_timeout');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('theme_approval_workflows', function (Blueprint $table) {
            $table->dropColumn([
                'step_timeout_hours',
                'escalation_roles',
                'auto_approve_after_timeout'
            ]);
            
            $table->dropIndex(['step_timeout_hours']);
            $table->dropIndex(['auto_approve_after_timeout']);
        });
    }
};
