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
            $table->foreignId('user_id')->constrained();
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
                if (!Schema::hasColumn('pages', 'user_id')) {
                    $table->foreignId('user_id')->constrained()->after('blocks');
                }
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('pages');
    }
};