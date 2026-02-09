<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_salaries', function (Blueprint $table) {
            if (!Schema::hasColumn('user_salaries', 'gaji_harian_mode')) {
                $table->enum('gaji_harian_mode', ['manual', 'pokok', 'pokok_plus_tunjangan'])
                    ->default('manual')
                    ->after('gaji_harian');
            }

            if (!Schema::hasColumn('user_salaries', 'auto_generate_harian')) {
                $table->boolean('auto_generate_harian')
                    ->default(false)
                    ->after('gaji_harian_mode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_salaries', function (Blueprint $table) {
            $dropColumns = [];

            if (Schema::hasColumn('user_salaries', 'auto_generate_harian')) {
                $dropColumns[] = 'auto_generate_harian';
            }

            if (Schema::hasColumn('user_salaries', 'gaji_harian_mode')) {
                $dropColumns[] = 'gaji_harian_mode';
            }

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
