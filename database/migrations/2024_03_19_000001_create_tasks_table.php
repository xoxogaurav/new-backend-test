<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->decimal('reward', 10, 2);
            $table->string('time_estimate');
            $table->integer('time_in_seconds');
            $table->string('category');
            $table->enum('difficulty', ['Easy', 'Medium', 'Hard']);
            $table->json('steps');
            $table->enum('approval_type', ['automatic', 'manual']);
            $table->boolean('is_active')->default(true);
            $table->integer('total_submission_limit');
            $table->integer('daily_submission_limit');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};