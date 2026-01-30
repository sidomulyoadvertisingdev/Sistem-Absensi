<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Periode gaji
            $table->unsignedTinyInteger('bulan'); // 1 - 12
            $table->unsignedSmallInteger('tahun');

            // Nilai akhir gaji
            $table->decimal('total_gaji', 15, 2)->default(0);

            // Status pembayaran
            $table->enum('status', ['draft', 'dibayar'])
                ->default('draft');

            // Waktu pembayaran
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            // 🔒 Satu payroll per user per bulan
            $table->unique(['user_id', 'bulan', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
