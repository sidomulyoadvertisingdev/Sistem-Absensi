<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalaryDeductionRuleSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $rules = [
            [
                'kode' => 'SALAH_CETAK',
                'nama' => 'Kesalahan Cetak',
                'penempatan' => null, // 🔥 berlaku global
                'keterangan' => 'Kerugian akibat kesalahan cetak ditanggung karyawan',
                'type' => 'fixed',
                'value' => 0,
                'base_amount' => 'salary_kotor',
                'condition_type' => 'pelanggaran',
                'condition_value' => 1,
                'aktif' => true,
            ],
            [
                'kode' => 'OFF_LEBIH_5',
                'nama' => 'Off Lebih dari 5 Hari',
                'penempatan' => null, // 🔥 global
                'keterangan' => 'Potongan 10% jika off lebih dari 5 hari',
                'type' => 'percentage',
                'value' => 10,
                'base_amount' => 'salary_kotor',
                'condition_type' => 'off_day',
                'condition_value' => 5,
                'aktif' => true,
            ],
            [
                'kode' => 'TELAT_3X',
                'nama' => 'Terlambat 3 Kali',
                'penempatan' => null, // 🔥 global
                'keterangan' => 'Potongan 5% dari gaji pokok jika terlambat 3 kali',
                'type' => 'percentage',
                'value' => 5,
                'base_amount' => 'gaji_pokok',
                'condition_type' => 'terlambat',
                'condition_value' => 3,
                'aktif' => true,
            ],
        ];

        foreach ($rules as $rule) {
            DB::table('salary_deduction_rules')->updateOrInsert(
                ['kode' => $rule['kode']], // UNIQUE KEY
                array_merge($rule, [
                    'updated_at' => $now,
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ])
            );
        }
    }
}
