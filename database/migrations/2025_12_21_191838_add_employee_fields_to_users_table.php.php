<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // ❌ JANGAN TAMBAH role (SUDAH ADA)

            if (!Schema::hasColumn('users', 'nik')) {
                $table->string('nik')->unique()->after('name');
            }

            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }

            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }

            if (!Schema::hasColumn('users', 'jabatan')) {
                $table->string('jabatan')->nullable()->after('address');
            }

            if (!Schema::hasColumn('users', 'penempatan')) {
                $table->enum('penempatan', ['SM Lecy', 'Gudang', 'SM'])
                      ->default('SM Lecy')
                      ->after('jabatan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nik',
                'phone',
                'address',
                'jabatan',
                'penempatan',
            ]);
        });
    }
};
