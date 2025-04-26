<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('pages')) {
            Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->json('blocks');
            $table->json('ai_metadata')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            });
        } else {
            Schema::table('pages', function (Blueprint $table) {
                if (!Schema::hasColumn('pages', 'title')) {
                    $table->string('title')->after('id');
                }
                if (!Schema::hasColumn('pages', 'slug')) {
                    $table->string('slug')->unique()->after('title');
                }
                if (!Schema::hasColumn('pages', 'blocks')) {
                    $table->json('blocks')->after('slug');
                }
                if (!Schema::hasColumn('pages', 'ai_metadata')) {
                    $table->json('ai_metadata')->nullable()->after('blocks');
                }
                if (!Schema::hasColumn('pages', 'user_id')) {
                    $table->foreignId('user_id')->constrained()->onDelete('cascade')->after('ai_metadata');
                }
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('pages');
    }
};