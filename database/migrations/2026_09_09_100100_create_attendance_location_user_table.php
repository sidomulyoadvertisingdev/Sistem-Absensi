<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Pivot: lokasi absensi mana saja yang boleh dipakai oleh tiap user.
     */
    public function up(): void
    {
        Schema::create('attendance_location_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_location_id')
                ->constrained('attendance_locations')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['attendance_location_id', 'user_id'], 'attendance_location_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_location_user');
    }
};
