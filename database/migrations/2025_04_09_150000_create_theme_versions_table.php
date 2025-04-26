<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('theme_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('theme_id')->constrained()->cascadeOnDelete();
            $table->string('version')->index();
            $table->json('manifest')->nullable();
            $table->text('changelog')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            
            $table->unique(['theme_id', 'version']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('theme_versions');
    }
};
