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
        Schema::table('jobs', function (Blueprint $table) {

            if (!Schema::hasColumn('jobs', 'title')) {
                $table->string('title');
            }

            if (!Schema::hasColumn('jobs', 'description')) {
                $table->text('description')->nullable();
            }

            if (!Schema::hasColumn('jobs', 'thumbnail')) {
                $table->string('thumbnail')->nullable();
            }

            if (!Schema::hasColumn('jobs', 'location')) {
                $table->string('location')->nullable();
            }

            if (!Schema::hasColumn('jobs', 'job_type')) {
                $table->string('job_type')->nullable();
            }

            if (!Schema::hasColumn('jobs', 'deadline')) {
                $table->date('deadline')->nullable();
            }

            if (!Schema::hasColumn('jobs', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {

            if (Schema::hasColumn('jobs', 'title')) {
                $table->dropColumn('title');
            }

            if (Schema::hasColumn('jobs', 'description')) {
                $table->dropColumn('description');
            }

            if (Schema::hasColumn('jobs', 'thumbnail')) {
                $table->dropColumn('thumbnail');
            }

            if (Schema::hasColumn('jobs', 'location')) {
                $table->dropColumn('location');
            }

            if (Schema::hasColumn('jobs', 'job_type')) {
                $table->dropColumn('job_type');
            }

            if (Schema::hasColumn('jobs', 'deadline')) {
                $table->dropColumn('deadline');
            }

            if (Schema::hasColumn('jobs', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
