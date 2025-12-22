<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // database/migrations/xxxx_add_manual_fields_to_absensis_table.php
        Schema::table('absensis', function (Blueprint $table) {
            $table->boolean('input_by_admin')->default(false);
            $table->foreignId('admin_id')->nullable()->constrained('users');
            $table->text('keterangan_manual')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            //
        });
    }
};
