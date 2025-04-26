<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('error_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('severity')->default('medium'); // low, medium, high, critical
            $table->string('color')->default('#6b7280'); // Tailwind gray-500
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('error_classifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('error_category_id')->constrained();
            $table->morphs('error_source'); // Can attach to ExportHistory or other error sources
            $table->text('error_message');
            $table->foreignId('classified_by')->nullable()->constrained('users');
            $table->boolean('auto_classified')->default(false);
            $table->decimal('confidence', 5, 2)->nullable(); // For auto-classification confidence
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('error_classifications');
        Schema::dropIfExists('error_categories');
    }
};