<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('analytics_exports', function (Blueprint $table) {
            if (!Schema::hasColumn('analytics_exports', 'anonymize')) {
                $table->boolean('anonymize')->default(false);
            }
            if (!Schema::hasColumn('analytics_exports', 'anonymization_options')) {
                $table->json('anonymization_options')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('analytics_exports', function (Blueprint $table) {
            $table->dropColumn(['anonymize', 'anonymization_options']);
        });
    }
};