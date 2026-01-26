<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_salaries', function (Blueprint $table) {

            // TAMBAH KOLOM TUNJANGAN
            $table->bigInteger('tunjangan_umum')->default(0)->after('gaji_pokok');
            $table->bigInteger('tunjangan_transport')->default(0)->after('tunjangan_umum');
            $table->bigInteger('tunjangan_thr')->default(0)->after('tunjangan_transport');
            $table->bigInteger('tunjangan_kesehatan')->default(0)->after('tunjangan_thr');

            // PASTIKAN LEMBUR ADA
            if (!Schema::hasColumn('user_salaries', 'lembur_per_jam')) {
                $table->bigInteger('lembur_per_jam')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_salaries', function (Blueprint $table) {
            $table->dropColumn([
                'tunjangan_umum',
                'tunjangan_transport',
                'tunjangan_thr',
                'tunjangan_kesehatan',
            ]);
        });
    }
};
