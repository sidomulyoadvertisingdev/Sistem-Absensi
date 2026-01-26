<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('salary_deduction_rules', function (Blueprint $table) {
            $table->id();

            // KODE UNIK ATURAN
            $table->string('kode')->unique();

            // NAMA ATURAN
            $table->string('nama');

            // DESKRIPSI
            $table->text('deskripsi')->nullable();

            /**
             * TIPE ATURAN
             * - pelanggaran
             * - absensi
             * - keterlambatan
             */
            $table->enum('kategori', [
                'pelanggaran',
                'absensi',
                'keterlambatan'
            ]);

            /**
             * TIPE PERHITUNGAN
             * - nominal  → potong langsung (contoh: salah cetak)
             * - per_hari → potong per hari (off > 5 hari)
             * - per_kasus → potong per kejadian
             * - akumulasi → potong jika melewati batas
             */
            $table->enum('tipe', [
                'nominal',
                'per_hari',
                'per_kasus',
                'akumulasi'
            ]);

            // BATAS (misal: 5 hari off, 3x telat)
            $table->integer('batas')->nullable();

            // NOMINAL POTONGAN
            $table->integer('nominal')->default(0);

            // STATUS
            $table->boolean('aktif')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_deduction_rules');
    }
};
