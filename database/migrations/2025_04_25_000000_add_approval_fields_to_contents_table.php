<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            if (!Schema::hasColumn('contents', 'approval_status')) {
                $table->string('approval_status')->default('draft')
                    ->comment('draft, pending_review, approved, rejected, changes_requested');
            }
            if (!Schema::hasColumn('contents', 'reviewer_id')) {
                $table->foreignId('reviewer_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn('contents', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable();
            }
            if (!Schema::hasColumn('contents', 'review_notes')) {
                $table->text('review_notes')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn(['approval_status', 'reviewer_id', 'reviewed_at', 'review_notes']);
        });
    }
};