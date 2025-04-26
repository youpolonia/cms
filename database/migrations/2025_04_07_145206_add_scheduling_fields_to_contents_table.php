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
            $table->timestamp('publish_at')->nullable()->after('is_published');
            $table->timestamp('expire_at')->nullable()->after('publish_at');
            $table->enum('status', ['draft', 'review', 'published', 'archived'])
                  ->default('draft')
                  ->after('expire_at');
            $table->boolean('is_recurring')->default(false)->after('status');
            $table->string('recurrence_frequency')->nullable()->after('is_recurring');
            $table->timestamp('recurrence_end_date')->nullable()->after('recurrence_frequency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn(['publish_at', 'expire_at', 'status']);
        });
    }
};
