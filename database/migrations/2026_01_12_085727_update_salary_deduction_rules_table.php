<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('salary_deduction_rules', function (Blueprint $table) {

            // TYPE POTONGAN: FIXED / PERCENTAGE
            if (!Schema::hasColumn('salary_deduction_rules', 'type')) {
                $table->enum('type', ['fixed', 'percentage'])
                      ->default('fixed')
                      ->after('nama');
            }

            // NILAI POTONGAN
            if (!Schema::hasColumn('salary_deduction_rules', 'value')) {
                $table->decimal('value', 10, 2)
                      ->default(0)
                      ->after('type');
            }

            // DASAR PERHITUNGAN %
            if (!Schema::hasColumn('salary_deduction_rules', 'base_amount')) {
                $table->enum('base_amount', [
                    'gaji_pokok',
                    'salary_kotor',
                    'total_gaji'
                ])->nullable()->after('value');
            }

            // KONDISI TRIGGER
            if (!Schema::hasColumn('salary_deduction_rules', 'condition_type')) {
                $table->enum('condition_type', [
                    'terlambat',
                    'off_day',
                    'pelanggaran'
                ])->nullable()->after('base_amount');
            }

            // NILAI KONDISI
            if (!Schema::hasColumn('salary_deduction_rules', 'condition_value')) {
                $table->integer('condition_value')
                      ->default(0)
                      ->after('condition_type');
            }

            // STATUS
            if (!Schema::hasColumn('salary_deduction_rules', 'active')) {
                $table->boolean('active')
                      ->default(true)
                      ->after('condition_value');
            }
        });
    }

    public function down(): void
    {
        // rollback optional (aman dikosongkan)
    }
};
