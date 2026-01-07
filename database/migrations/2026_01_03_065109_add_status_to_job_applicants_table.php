<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applicants', function (Blueprint $table) {
            $table->string('status')
                ->default('pending')
                ->after('answers');
        });
    }

    public function down(): void
    {
        Schema::table('job_applicants', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
