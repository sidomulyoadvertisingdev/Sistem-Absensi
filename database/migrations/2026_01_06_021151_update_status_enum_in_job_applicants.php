<?php

// database/migrations/xxxx_update_status_enum_in_job_applicants.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE job_applicants
                MODIFY status ENUM(
                    'pending',
                    'review',
                    'interview',
                    'training',
                    'accepted',
                    'rejected'
                ) DEFAULT 'pending'
            ");
        }
    }

    public function down()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE job_applicants
                MODIFY status ENUM(
                    'pending',
                    'accepted',
                    'rejected'
                ) DEFAULT 'pending'
            ");
        }
    }
};
