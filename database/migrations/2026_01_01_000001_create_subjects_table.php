<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Performs up in 2026_01_01_000001_create_subjects_table. */
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('instructor')->nullable();
            $table->string('room')->nullable();
            $table->string('schedule')->nullable();
            $table->string('category')->default('Core');
            $table->string('color')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /** Performs down in 2026_01_01_000001_create_subjects_table. */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
