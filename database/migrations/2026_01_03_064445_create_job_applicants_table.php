<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applicants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_id')
                ->constrained('job_vacancies')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();

            // data jawaban form (json)
            $table->json('answers')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applicants');
    }
};
