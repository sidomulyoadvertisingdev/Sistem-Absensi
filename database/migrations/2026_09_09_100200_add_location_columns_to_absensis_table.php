<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Simpan lokasi absensi yang dipakai + koordinat GPS saat masuk/pulang
     * (audit trail geofence dari aplikasi mobile).
     */
    public function up(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->foreignId('attendance_location_id')
                ->nullable()
                ->after('user_id')
                ->constrained('attendance_locations')
                ->nullOnDelete();

            $table->decimal('lat_masuk', 10, 7)->nullable()->after('jam_masuk');
            $table->decimal('lng_masuk', 10, 7)->nullable()->after('lat_masuk');
            $table->decimal('lat_pulang', 10, 7)->nullable()->after('jam_pulang');
            $table->decimal('lng_pulang', 10, 7)->nullable()->after('lat_pulang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropConstrainedForeignId('attendance_location_id');
            $table->dropColumn(['lat_masuk', 'lng_masuk', 'lat_pulang', 'lng_pulang']);
        });
    }
};
