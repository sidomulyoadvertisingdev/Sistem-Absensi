<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submission_types', function (Blueprint $table) {
            $table->boolean('is_izin_pulang_awal')
                ->default(false)
                ->after('butuh_lampiran');
        });
    }

    public function down(): void
    {
        Schema::table('submission_types', function (Blueprint $table) {
            $table->dropColumn('is_izin_pulang_awal');
        });
    }
};
