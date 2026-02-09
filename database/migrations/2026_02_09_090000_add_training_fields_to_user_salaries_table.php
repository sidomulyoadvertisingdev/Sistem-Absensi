<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_salaries', function (Blueprint $table) {
            if (!Schema::hasColumn('user_salaries', 'training_enabled')) {
                $table->boolean('training_enabled')
                    ->default(false)
                    ->after('include_tunjangan');
            }

            if (!Schema::hasColumn('user_salaries', 'training_start_date')) {
                $table->date('training_start_date')
                    ->nullable()
                    ->after('training_enabled');
            }

            if (!Schema::hasColumn('user_salaries', 'training_duration_days')) {
                $table->unsignedInteger('training_duration_days')
                    ->default(0)
                    ->after('training_start_date');
            }

            if (!Schema::hasColumn('user_salaries', 'training_deduction_type')) {
                $table->enum('training_deduction_type', ['percentage', 'fixed'])
                    ->default('percentage')
                    ->after('training_duration_days');
            }

            if (!Schema::hasColumn('user_salaries', 'training_deduction_value')) {
                $table->decimal('training_deduction_value', 12, 2)
                    ->default(0)
                    ->after('training_deduction_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_salaries', function (Blueprint $table) {
            $dropColumns = [];

            if (Schema::hasColumn('user_salaries', 'training_deduction_value')) {
                $dropColumns[] = 'training_deduction_value';
            }

            if (Schema::hasColumn('user_salaries', 'training_deduction_type')) {
                $dropColumns[] = 'training_deduction_type';
            }

            if (Schema::hasColumn('user_salaries', 'training_duration_days')) {
                $dropColumns[] = 'training_duration_days';
            }

            if (Schema::hasColumn('user_salaries', 'training_start_date')) {
                $dropColumns[] = 'training_start_date';
            }

            if (Schema::hasColumn('user_salaries', 'training_enabled')) {
                $dropColumns[] = 'training_enabled';
            }

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
