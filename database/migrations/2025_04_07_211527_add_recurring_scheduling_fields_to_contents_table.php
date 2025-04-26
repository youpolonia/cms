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
        Schema::table('contents', function (Blueprint $table) {
            if (!Schema::hasColumn('contents', 'is_recurring')) {
                $table->boolean('is_recurring')->default(false)->after('status');
            }
            if (!Schema::hasColumn('contents', 'recurring_frequency')) {
                $table->enum('recurring_frequency', ['daily', 'weekly', 'monthly', 'yearly'])
                      ->nullable()
                      ->after('is_recurring');
            }
            if (!Schema::hasColumn('contents', 'recurring_end')) {
                $table->timestamp('recurring_end')->nullable()->after('recurring_frequency');
            }
            if (!Schema::hasColumn('contents', 'last_published_at')) {
                $table->timestamp('last_published_at')->nullable()->after('recurring_end');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('contents', 'is_recurring')) {
                $columnsToDrop[] = 'is_recurring';
            }
            if (Schema::hasColumn('contents', 'recurring_frequency')) {
                $columnsToDrop[] = 'recurring_frequency';
            }
            if (Schema::hasColumn('contents', 'recurring_end')) {
                $columnsToDrop[] = 'recurring_end';
            }
            if (Schema::hasColumn('contents', 'last_published_at')) {
                $columnsToDrop[] = 'last_published_at';
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
