<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_salaries', function (Blueprint $table) {
            $table->integer('bonus')->default(0)->after('transport');
        });
    }

    public function down(): void
    {
        Schema::table('user_salaries', function (Blueprint $table) {
            $table->dropColumn('bonus');
        });
    }
};
