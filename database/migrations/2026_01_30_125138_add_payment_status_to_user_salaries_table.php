<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_salaries', function (Blueprint $table) {

            // JANGAN TAMBAH is_paid LAGI
            if (!Schema::hasColumn('user_salaries', 'paid_at')) {
                $table->timestamp('paid_at')
                    ->nullable()
                    ->after('is_paid');
            }

            if (!Schema::hasColumn('user_salaries', 'paid_by')) {
                $table->foreignId('paid_by')
                    ->nullable()
                    ->after('paid_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_salaries', function (Blueprint $table) {
            if (Schema::hasColumn('user_salaries', 'paid_by')) {
                $table->dropForeign(['paid_by']);
                $table->dropColumn('paid_by');
            }

            if (Schema::hasColumn('user_salaries', 'paid_at')) {
                $table->dropColumn('paid_at');
            }
        });
    }
};
