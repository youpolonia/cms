<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('content_versions', function (Blueprint $table) {
            // Only change version_number back to integer if needed
            $table->integer('version_number')->change();
        });
    }

    public function down()
    {
        // No need to revert version_number change as it would break new code
    }
};