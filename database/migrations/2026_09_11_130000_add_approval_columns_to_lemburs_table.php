<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lemburs', function (Blueprint $table) {
            // ✅ APPROVAL GAJI — terpisah dari `status` (yang menggambarkan progres kerja).
            // NULL = belum disetujui admin, nominal lembur masih pending (tidak masuk gaji).
            // Terisi = admin sudah menyetujui, nominal lembur masuk perhitungan gaji.
            $table->timestamp('approved_at')->nullable()->after('status');

            $table->foreignId('approved_by')
                ->nullable()
                ->after('approved_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lemburs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn('approved_at');
        });
    }
};
