<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                // 🔑 IDENTITAS ADMIN
                'email' => 'admin@sidomulyoproject.com',
            ],
            [
                'name'       => 'Administrator',
                'password'   => Hash::make('Advertising@01'),
                'role'       => 'admin',

                // ✅ DATA KARYAWAN LENGKAP
                'nik'        => 'ADM0001',
                'phone'      => '081234567890',
                'address'    => 'Kantor Pusat Sido Mulyo Project',
                'jabatan'    => 'Administrator Sistem',
                'penempatan' => 'SM Lecy',
            ]
        );
    }
}
