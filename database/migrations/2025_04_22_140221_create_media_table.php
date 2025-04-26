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
        if (!Schema::hasTable('media')) {
            Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('file_path');
            $table->string('thumbnail_path')->nullable();
            $table->string('mime_type');
            $table->integer('size');
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->text('alt_text')->nullable();
            $table->text('caption')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            });
        } else {
            Schema::table('media', function (Blueprint $table) {
                if (!Schema::hasColumn('media', 'thumbnail_path')) {
                    $table->string('thumbnail_path')->nullable()->after('file_path');
                }
                if (!Schema::hasColumn('media', 'width')) {
                    $table->integer('width')->nullable()->after('size');
                }
                if (!Schema::hasColumn('media', 'height')) {
                    $table->integer('height')->nullable()->after('width');
                }
                if (!Schema::hasColumn('media', 'alt_text')) {
                    $table->text('alt_text')->nullable()->after('height');
                }
                if (!Schema::hasColumn('media', 'caption')) {
                    $table->text('caption')->nullable()->after('alt_text');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
