<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('themes')) {
            Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('version_history')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            });
        } else {
            Schema::table('themes', function (Blueprint $table) {
                if (!Schema::hasColumn('themes', 'name')) {
                    $table->string('name')->after('id');
                }
                if (!Schema::hasColumn('themes', 'description')) {
                    $table->text('description')->nullable()->after('name');
                }
                if (!Schema::hasColumn('themes', 'version_history')) {
                    $table->json('version_history')->nullable()->after('description');
                }
                if (!Schema::hasColumn('themes', 'is_active')) {
                    $table->boolean('is_active')->default(false)->after('version_history');
                }
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('themes');
    }
};