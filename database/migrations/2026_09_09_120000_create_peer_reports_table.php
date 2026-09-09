<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peer_reports', function (Blueprint $table) {
            $table->id();

            // Pelapor — HANYA admin yang boleh melihat identitas ini.
            $table->foreignId('reporter_id')->nullable()->constrained('users')->nullOnDelete();
            // Karyawan yang dilaporkan.
            $table->foreignId('reported_user_id')->constrained('users')->cascadeOnDelete();

            $table->string('kategori', 60);
            $table->string('lokasi')->nullable();
            $table->text('kronologi');
            $table->string('bukti_path')->nullable();

            // pending | approved | rejected
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('catatan_admin')->nullable();

            $table->timestamps();

            $table->index(['reported_user_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peer_reports');
    }
};
