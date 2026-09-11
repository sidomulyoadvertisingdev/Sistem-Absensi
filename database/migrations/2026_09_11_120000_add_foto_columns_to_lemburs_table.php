<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lemburs', function (Blueprint $table) {
            // 📸 BUKTI FOTO — diambil dari kamera saat pengajuan & saat selesai lembur
            $table->string('foto_mulai')->nullable()->after('jam_mulai');
            $table->string('foto_selesai')->nullable()->after('jam_selesai');
        });
    }

    public function down(): void
    {
        Schema::table('lemburs', function (Blueprint $table) {
            $table->dropColumn(['foto_mulai', 'foto_selesai']);
        });
    }
};
