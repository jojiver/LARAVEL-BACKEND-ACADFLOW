<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Performs up in 2026_01_01_000003_create_tasks_table. */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('type')->default('Assignment');
            $table->text('description')->nullable();
            $table->dateTime('deadline');
            $table->decimal('estimated_hours', 5, 2)->default(1);
            $table->string('difficulty')->default('Medium');
            $table->string('importance')->default('Medium');
            $table->unsignedTinyInteger('progress')->default(0);
            $table->string('status')->default('Not started');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'deadline']);
            $table->index('subject_id');
        });
    }

    /** Performs down in 2026_01_01_000003_create_tasks_table. */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
