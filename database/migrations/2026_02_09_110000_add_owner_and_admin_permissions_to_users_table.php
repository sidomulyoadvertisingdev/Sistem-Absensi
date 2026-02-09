<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            DB::statement("
                ALTER TABLE users
                MODIFY role ENUM(
                    'owner',
                    'admin',
                    'admin_staff',
                    'hrd',
                    'keuangan',
                    'karyawan',
                    'user'
                )
                NOT NULL
                DEFAULT 'user'
            ");
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'admin_permissions')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('admin_permissions')
                    ->nullable()
                    ->after('role');
            });
        }

        // Jika belum ada owner, jadikan admin pertama sebagai owner.
        $hasOwner = DB::table('users')->where('role', 'owner')->exists();
        if (!$hasOwner) {
            $firstAdminId = DB::table('users')
                ->where('role', 'admin')
                ->orderBy('id')
                ->value('id');

            if ($firstAdminId) {
                DB::table('users')
                    ->where('id', $firstAdminId)
                    ->update(['role' => 'owner']);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'admin_permissions')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('admin_permissions');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            DB::table('users')
                ->whereIn('role', ['owner', 'admin_staff', 'hrd', 'keuangan'])
                ->update(['role' => 'admin']);

            DB::statement("
                ALTER TABLE users
                MODIFY role ENUM('admin', 'karyawan', 'user')
                NOT NULL
                DEFAULT 'user'
            ");
        }
    }
};

