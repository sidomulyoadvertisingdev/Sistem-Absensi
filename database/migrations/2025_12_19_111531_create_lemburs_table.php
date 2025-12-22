<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lemburs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('tanggal');

            // ⏰ JAM LEMBUR
            $table->time('jam_mulai');
            $table->time('jam_selesai');

            // 📝 KETERANGAN
            $table->text('keterangan')->nullable();

            // ✅ STATUS (PAKAI VARCHAR, JANGAN ENUM)
            $table->string('status', 20)->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lemburs');
    }
};
