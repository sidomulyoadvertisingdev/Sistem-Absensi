<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_todos', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('description')->nullable();

            // Bonus per job (langsung masuk gaji saat selesai)
            $table->unsignedBigInteger('bonus')->default(0);

            // true = broadcast ke semua karyawan
            // false = assign langsung ke user tertentu
            $table->boolean('broadcast')->default(false);

            // open | closed
            $table->enum('status', ['open', 'closed'])->default('open');

            $table->timestamps();

            // Indexing
            $table->index('broadcast');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_todos');
    }
};
