<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Performs up in 2026_01_01_000004_create_task_steps_table. */
    public function up(): void
    {
        Schema::create('task_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->boolean('done')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index('task_id');
        });
    }

    /** Performs down in 2026_01_01_000004_create_task_steps_table. */
    public function down(): void
    {
        Schema::dropIfExists('task_steps');
    }
};
