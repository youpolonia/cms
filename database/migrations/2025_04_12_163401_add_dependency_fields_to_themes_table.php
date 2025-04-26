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
        Schema::table('themes', function (Blueprint $table) {
            $table->json('dependencies')->nullable()->after('marketplace_metadata');
            $table->boolean('requirements_met')->default(false)->after('dependencies');
            $table->json('min_system_requirements')->nullable()->after('requirements_met');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->dropColumn(['dependencies', 'requirements_met', 'min_system_requirements']);
        });
    }
};
