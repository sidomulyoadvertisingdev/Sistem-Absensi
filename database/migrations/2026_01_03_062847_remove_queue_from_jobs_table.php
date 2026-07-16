<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            if (Schema::hasColumn('jobs', 'queue')) {
                // SQLite gagal drop kolom yang masih dirujuk oleh index,
                // jadi index harus dihapus lebih dulu.
                if (DB::getDriverName() === 'sqlite') {
                    $indexes = collect(DB::select("PRAGMA index_list('jobs')"))->pluck('name');
                    if ($indexes->contains('jobs_queue_index')) {
                        $table->dropIndex('jobs_queue_index');
                    }
                }

                $table->dropColumn('queue');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->string('queue')->nullable();
        });
    }
};
