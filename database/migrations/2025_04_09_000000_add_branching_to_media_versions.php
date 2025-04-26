<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('media_versions', function (Blueprint $table) {
            $table->string('branch_name')->nullable()->after('comment');
            $table->foreignId('parent_version_id')->nullable()
                ->after('branch_name')
                ->constrained('media_versions')
                ->onDelete('set null');
            $table->boolean('is_merged')->default(false)->after('parent_version_id');
            $table->timestamp('merged_at')->nullable()->after('is_merged');
        });

        Schema::create('media_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->foreignId('base_version_id')
                ->nullable()
                ->constrained('media_versions')
                ->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::table('media_versions', function (Blueprint $table) {
            $table->dropForeign(['parent_version_id']);
            $table->dropColumn(['branch_name', 'parent_version_id', 'is_merged', 'merged_at']);
        });

        Schema::dropIfExists('media_branches');
    }
};
