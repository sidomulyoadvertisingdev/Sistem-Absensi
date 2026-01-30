<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_salaries', function (Blueprint $table) {

            // status gaji sudah dibayar atau belum
            $table->boolean('is_paid')
                ->default(false)
                ->after('aktif');

            // waktu gaji dibayarkan
            $table->timestamp('paid_at')
                ->nullable()
                ->after('is_paid');

            // periode gaji (contoh: 2026-01)
            $table->string('payroll_period', 7)
                ->nullable()
                ->after('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_salaries', function (Blueprint $table) {
            $table->dropColumn([
                'is_paid',
                'paid_at',
                'payroll_period',
            ]);
        });
    }
};
