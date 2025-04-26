<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('theme_approval_workflows', function (Blueprint $table) {
            $table->integer('required_approvers_count')->default(0);
            $table->json('required_roles')->nullable();
            $table->boolean('sequential_approval')->default(false);
        });
    }

    public function down()
    {
        Schema::table('theme_approval_workflows', function (Blueprint $table) {
            $table->dropColumn([
                'required_approvers_count',
                'required_roles',
                'sequential_approval'
            ]);
        });
    }
};
