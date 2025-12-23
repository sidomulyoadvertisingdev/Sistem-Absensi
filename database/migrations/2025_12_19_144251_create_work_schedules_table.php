<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();

            // Relasi ke user
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Hari kerja
            $table->enum('hari', [
                'senin',
                'selasa',
                'rabu',
                'kamis',
                'jumat',
                'sabtu',
                'minggu'
            ]);

            // Jam kerja (nullable = hari libur)
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();

            // Jam istirahat
            $table->time('istirahat_mulai')->nullable();
            $table->time('istirahat_selesai')->nullable();

            // Status hari
            $table->boolean('aktif')->default(true); // false = LIBUR

            $table->timestamps();

            // 1 user hanya boleh punya 1 jadwal per hari
            $table->unique(['user_id', 'hari']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};
