<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // drop dulu kalau enum lama salah
            $table->dropColumn('penempatan');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('penempatan', [
                'SM Lecy',
                'SM Percetakan',
                'SM Gudang',
            ])->after('jabatan');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('penempatan');
            $table->string('penempatan', 50)->nullable();
        });
    }
};
