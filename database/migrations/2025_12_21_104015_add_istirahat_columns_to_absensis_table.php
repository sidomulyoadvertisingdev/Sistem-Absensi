<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->time('istirahat_mulai')->nullable()->after('jam_masuk');
            $table->time('istirahat_selesai')->nullable()->after('istirahat_mulai');
        });
    }

    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropColumn(['istirahat_mulai', 'istirahat_selesai']);
        });
    }
};
