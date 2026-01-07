<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_todo_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_todo_id')
                ->constrained('job_todos')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // status pengerjaan job
            // pending   = ditugaskan / menunggu
            // accepted  = sedang dikerjakan
            // completed = selesai (bonus masuk gaji)
            $table->enum('status', ['pending', 'accepted', 'completed'])
                ->default('pending');

            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            // Satu user tidak boleh punya job yang sama 2x
            $table->unique(['job_todo_id', 'user_id']);

            // Index untuk performa dashboard
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_todo_user');
    }
};
