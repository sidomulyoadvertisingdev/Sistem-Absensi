<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_form_fields', function (Blueprint $table) {
            $table->id();

            // relasi ke jobs (BENAR)
            $table->foreignId('job_id')
                ->constrained('jobs')
                ->cascadeOnDelete();

            $table->string('label');
            $table->string('name');
            $table->string('type'); // text, textarea, file, select
            $table->boolean('required')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_form_fields');
    }
};
