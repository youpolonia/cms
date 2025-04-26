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
        if (!Schema::hasTable('content_moderations')) {
            Schema::create('content_moderations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, approved, rejected, flagged
            $table->json('scores')->nullable(); // JSON of moderation scores
            $table->json('flags')->nullable(); // JSON of flagged categories
            $table->text('moderation_notes')->nullable();
            $table->foreignId('moderator_id')->nullable()->constrained('users');
            $table->timestamp('moderated_at')->nullable();
            $table->timestamps();
            });
        } else {
            Schema::table('content_moderations', function (Blueprint $table) {
                if (!Schema::hasColumn('content_moderations', 'content_id')) {
                    $table->foreignId('content_id')->constrained()->cascadeOnDelete()->after('id');
                }
                if (!Schema::hasColumn('content_moderations', 'status')) {
                    $table->string('status')->default('pending')->after('content_id');
                }
                if (!Schema::hasColumn('content_moderations', 'scores')) {
                    $table->json('scores')->nullable()->after('status');
                }
                if (!Schema::hasColumn('content_moderations', 'flags')) {
                    $table->json('flags')->nullable()->after('scores');
                }
                if (!Schema::hasColumn('content_moderations', 'moderation_notes')) {
                    $table->text('moderation_notes')->nullable()->after('flags');
                }
                if (!Schema::hasColumn('content_moderations', 'moderator_id')) {
                    $table->foreignId('moderator_id')->nullable()->constrained('users')->after('moderation_notes');
                }
                if (!Schema::hasColumn('content_moderations', 'moderated_at')) {
                    $table->timestamp('moderated_at')->nullable()->after('moderator_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_moderations');
    }
};
