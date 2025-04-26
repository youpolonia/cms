<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('approval_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('contents');
            $table->foreignId('step_id')->constrained('approval_steps');
            $table->foreignId('user_id')->constrained('users');
            $table->string('decision'); // approved, rejected, changes_requested
            $table->text('comments')->nullable();
            $table->json('changes_requested')->nullable();
            $table->timestamp('decision_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('approval_decisions');
    }
};