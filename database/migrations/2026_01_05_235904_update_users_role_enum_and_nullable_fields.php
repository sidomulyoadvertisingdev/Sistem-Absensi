<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * 1️⃣ Pastikan semua data role valid dulu
         * (kalau ada data aneh)
         */
        DB::table('users')
            ->whereNotIn('role', ['admin', 'karyawan', 'user'])
            ->update(['role' => 'user']);

        /**
         * 2️⃣ Ubah ENUM role (MySQL way – PALING AMAN)
         */
        DB::statement("
            ALTER TABLE users
            MODIFY role ENUM('admin','karyawan','user')
            NOT NULL
            DEFAULT 'user'
        ");

        /**
         * 3️⃣ Field khusus karyawan → wajib nullable
         * (supaya register user tidak error)
         */
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'nik')) {
                $table->string('nik')->nullable()->change();
            }

            if (Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->change();
            }

            if (Schema::hasColumn('users', 'address')) {
                $table->string('address')->nullable()->change();
            }

            if (Schema::hasColumn('users', 'jabatan')) {
                $table->string('jabatan')->nullable()->change();
            }

            if (Schema::hasColumn('users', 'penempatan')) {
                $table->string('penempatan')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        // ❗ Di production biasanya dikosongkan
        // rollback enum sangat berisiko
    }
};
