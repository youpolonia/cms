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
        Schema::table('theme_approval_steps', function (Blueprint $table) {
            $table->string('approval_logic')->nullable()->after('name');
            $table->string('rejection_logic')->nullable()->after('approval_logic');
            $table->integer('timeout_days')->nullable()->after('rejection_logic');
            $table->integer('required_approvals')->default(1)->after('timeout_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('theme_approval_steps', function (Blueprint $table) {
            $table->dropColumn(['approval_logic', 'rejection_logic', 'timeout_days', 'required_approvals']);
        });
    }
};
