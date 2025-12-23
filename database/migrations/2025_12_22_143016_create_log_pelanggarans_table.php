<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pelanggaran_logs', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('jabatan');
            $table->string('lokasi');

            $table->string('kode_pelanggaran');
            $table->string('jenis_pelanggaran');
            $table->string('kategori');

            $table->text('kronologi')->nullable();
            $table->string('bukti')->nullable();

            $table->string('tindakan')->nullable(); // SP1 / SP2 / SP3
            $table->text('catatan')->nullable();

            $table->string('penanggung_jawab');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggaran_logs');
    }
};
