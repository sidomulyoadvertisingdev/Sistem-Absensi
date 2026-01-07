<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Pakai DB::statement agar pasti mengubah tipe
        DB::statement("
            ALTER TABLE jobs
            MODIFY created_at DATETIME NULL,
            MODIFY updated_at DATETIME NULL
        ");
    }

    public function down(): void
    {
        // tidak perlu rollback tipe lama
    }
};
