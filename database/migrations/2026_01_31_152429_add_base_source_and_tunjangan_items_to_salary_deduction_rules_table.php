<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi
     */
    public function up(): void
    {
        Schema::table('salary_deduction_rules', function (Blueprint $table) {

            // 🔥 SUMBER POTONGAN
            // gaji_pokok | tunjangan
            if (!Schema::hasColumn('salary_deduction_rules', 'base_source')) {
                $table->string('base_source')
                    ->default('gaji_pokok')
                    ->after('base_amount');
            }

            // 🔥 DETAIL TUNJANGAN (JIKA base_source = tunjangan)
            // contoh: ["tunjangan_umum","tunjangan_transport"]
            if (!Schema::hasColumn('salary_deduction_rules', 'tunjangan_items')) {
                $table->json('tunjangan_items')
                    ->nullable()
                    ->after('base_source');
            }
        });
    }

    /**
     * Rollback migrasi
     */
    public function down(): void
    {
        Schema::table('salary_deduction_rules', function (Blueprint $table) {

            if (Schema::hasColumn('salary_deduction_rules', 'tunjangan_items')) {
                $table->dropColumn('tunjangan_items');
            }

            if (Schema::hasColumn('salary_deduction_rules', 'base_source')) {
                $table->dropColumn('base_source');
            }
        });
    }
};
