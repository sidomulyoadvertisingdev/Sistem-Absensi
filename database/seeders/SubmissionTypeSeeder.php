<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubmissionType;

class SubmissionTypeSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'kode' => 'RESIGN',
                'nama' => 'Pengunduran Diri',
                'deskripsi' => 'Pengajuan pengunduran diri dari perusahaan',
                'butuh_alasan' => true,
                'butuh_lampiran' => false,
                'is_izin_pulang_awal' => false,
            ],
            [
                'kode' => 'IZIN',
                'nama' => 'Izin Tidak Masuk',
                'deskripsi' => 'Izin tidak masuk kerja',
                'butuh_alasan' => true,
                'butuh_lampiran' => true,
                'is_izin_pulang_awal' => false,
            ],
            [
                'kode' => 'CUTI',
                'nama' => 'Cuti Tahunan',
                'deskripsi' => 'Pengajuan cuti tahunan',
                'butuh_alasan' => true,
                'butuh_lampiran' => false,
                'is_izin_pulang_awal' => false,
            ],
            [
                'kode' => 'MUTASI',
                'nama' => 'Mutasi',
                'deskripsi' => 'Pengajuan mutasi kerja',
                'butuh_alasan' => true,
                'butuh_lampiran' => false,
                'is_izin_pulang_awal' => false,
            ],
            [
                'kode' => 'SP_BANDING',
                'nama' => 'Pengajuan SP Banding',
                'deskripsi' => 'Pengajuan banding atas Surat Peringatan',
                'butuh_alasan' => true,
                'butuh_lampiran' => true,
                'is_izin_pulang_awal' => false,
            ],
            [
                'kode' => 'IZIN_PULANG_AWAL',
                'nama' => 'Izin Pulang Sebelum Jam Kerja',
                'deskripsi' => 'Pengajuan pulang lebih awal dan gaji dihitung proporsional jam kerja',
                'butuh_alasan' => true,
                'butuh_lampiran' => false,
                'is_izin_pulang_awal' => true,
            ],
        ];

        foreach ($data as $item) {
            SubmissionType::updateOrCreate(
                ['kode' => $item['kode']],
                $item
            );
        }
    }
}
