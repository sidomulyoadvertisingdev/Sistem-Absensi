<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('salary_deduction_rules', function (Blueprint $table) {
            $table->integer('max_occurrence')->nullable()
                ->after('condition_value')
                ->comment('Maksimal jumlah kejadian (contoh: 3x telat)');

            $table->integer('max_minutes')->nullable()
                ->after('max_occurrence')
                ->comment('Maksimal menit dihitung per kejadian');
        });
    }

    public function down(): void
    {
        Schema::table('salary_deduction_rules', function (Blueprint $table) {
            $table->dropColumn(['max_occurrence', 'max_minutes']);
        });
    }
};
