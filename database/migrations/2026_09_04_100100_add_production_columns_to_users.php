<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Outlet ERP tempat karyawan menutup produksi (diisi admin HR manual).
            $table->unsignedBigInteger('erp_outlet_id')->nullable()->after('penempatan');
            // Boleh mengambil & menutup job produksi dari aplikasi absensi.
            $table->boolean('can_close_production')->default(false)->after('erp_outlet_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['erp_outlet_id', 'can_close_production']);
        });
    }
};
