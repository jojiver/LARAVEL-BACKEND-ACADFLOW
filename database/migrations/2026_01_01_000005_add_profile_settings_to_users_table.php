<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('academic_term')->nullable()->after('year_level');
            $table->boolean('notify_deadline')->default(true)->after('academic_term');
            $table->boolean('notify_class')->default(true)->after('notify_deadline');
            $table->boolean('notify_priority')->default(true)->after('notify_class');
            $table->boolean('notify_workload')->default(true)->after('notify_priority');
            $table->string('reminder_timing')->default('1h')->after('notify_workload');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'academic_term',
                'notify_deadline',
                'notify_class',
                'notify_priority',
                'notify_workload',
                'reminder_timing',
            ]);
        });
    }
};
