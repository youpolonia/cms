<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('content_versions', function (Blueprint $table) {
            $table->timestamp('reviewed_at')->nullable()->after('approved_by');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->after('reviewed_at');
            $table->text('rejection_reason')->nullable()->after('reviewed_by');
        });
    }

    public function down()
    {
        Schema::table('content_versions', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['reviewed_at', 'reviewed_by', 'rejection_reason']);
        });
    }
};