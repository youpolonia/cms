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
        Schema::table('theme_ratings', function (Blueprint $table) {
            $table->string('marketplace_id')->nullable()->after('theme_id');
            $table->string('marketplace_source')->nullable()->after('marketplace_id');
            $table->string('theme_id')->nullable()->change();
            
            $table->index(['marketplace_id', 'marketplace_source']);
        });
    }

    public function down(): void
    {
        Schema::table('theme_ratings', function (Blueprint $table) {
            $table->dropIndex(['marketplace_id', 'marketplace_source']);
            $table->dropColumn(['marketplace_id', 'marketplace_source']);
            $table->string('theme_id')->nullable(false)->change();
        });
    }
};
