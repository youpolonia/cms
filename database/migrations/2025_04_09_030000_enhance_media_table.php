<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->string('mime_type')->after('path');
            $table->unsignedBigInteger('size')->after('mime_type');
            $table->string('disk')->default('public')->after('size');
            $table->string('alt_text')->nullable()->after('disk');
            $table->string('caption')->nullable()->after('alt_text');
            $table->boolean('is_public')->default(false)->after('caption');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn([
                'mime_type',
                'size',
                'disk',
                'alt_text',
                'caption',
                'is_public'
            ]);
        });
    }
};
