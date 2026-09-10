<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi SATU KALI setelah `app.timezone` diubah dari UTC ke Asia/Jakarta.
 *
 * Sebelumnya Laravel menulis `created_at`/`updated_at` dsb. sebagai waktu UTC.
 * Setelah zona aplikasi jadi WIB, nilai lama itu terbaca 7 jam lebih awal.
 * Command ini menggeser SEMUA kolom DATETIME/TIMESTAMP (bukan DATE / TIME)
 * sebesar selisih offset WIB terhadap UTC (+7 jam) supaya konsisten.
 *
 * Jalankan HANYA jika database berisi data yang ditulis saat app masih UTC.
 * Untuk instalasi baru (belum ada data lama), TIDAK perlu dijalankan.
 *
 *   php artisan app:shift-timestamps-to-wib --dry-run
 *   php artisan app:shift-timestamps-to-wib --force
 */
class ShiftTimestampsToWib extends Command
{
    protected $signature = 'app:shift-timestamps-to-wib
        {--dry-run : Tampilkan rencana tanpa mengubah data}
        {--force : Jalankan perubahan (wajib, tanpa ini otomatis dry-run)}
        {--tz= : Zona tujuan (default: config attendance_timezone)}';

    protected $description = 'Geser kolom datetime lama dari UTC ke WIB setelah perubahan app.timezone (sekali jalan).';

    /** Kolom yang TIDAK boleh digeser walau bertipe datetime. */
    private array $skipColumns = ['last_activity'];

    public function handle(): int
    {
        $tz = $this->option('tz') ?: config('app.attendance_timezone', 'Asia/Jakarta');
        // Selisih offset zona tujuan terhadap UTC, dalam jam bulat (WIB = +7).
        $offsetHours = intdiv(Carbon::now($tz)->utcOffset(), 60);

        if ($offsetHours === 0) {
            $this->warn("Offset {$tz} terhadap UTC = 0 jam. Tidak ada yang perlu digeser.");
            return self::SUCCESS;
        }

        $dryRun = ! $this->option('force') || $this->option('dry-run');
        $driver = DB::connection()->getDriverName();

        if ($driver !== 'mysql' && $driver !== 'mariadb') {
            $this->error("Command ini hanya mendukung MySQL/MariaDB (driver: {$driver}).");
            return self::FAILURE;
        }

        $marker = storage_path('app/.timestamps_shifted_to_wib');
        if (is_file($marker) && ! $this->option('dry-run')) {
            $this->error('Sudah pernah dijalankan (penanda: storage/app/.timestamps_shifted_to_wib). Batal.');
            return self::FAILURE;
        }

        $this->info(sprintf(
            '%s: geser kolom datetime %+d jam (UTC -> %s)',
            $dryRun ? 'DRY-RUN' : 'EKSEKUSI',
            $offsetHours,
            $tz
        ));

        $tables = collect(DB::select('SHOW TABLES'))
            ->map(fn ($row) => array_values((array) $row)[0])
            ->reject(fn ($t) => in_array($t, ['migrations', 'jobs', 'job_batches', 'failed_jobs', 'sessions', 'cache', 'cache_locks', 'password_reset_tokens'], true));

        $totalCols = 0;

        foreach ($tables as $table) {
            $columns = collect(DB::select("SHOW COLUMNS FROM `{$table}`"))
                ->filter(fn ($c) => preg_match('/^(datetime|timestamp)/i', $c->Type))
                ->pluck('Field')
                ->reject(fn ($f) => in_array($f, $this->skipColumns, true))
                ->values();

            if ($columns->isEmpty()) {
                continue;
            }

            foreach ($columns as $col) {
                $count = DB::table($table)->whereNotNull($col)->count();
                $totalCols++;
                $this->line(sprintf('  %-32s %-20s %6d baris', $table, $col, $count));

                if (! $dryRun && $count > 0) {
                    DB::statement(
                        "UPDATE `{$table}` SET `{$col}` = DATE_ADD(`{$col}`, INTERVAL ? HOUR) WHERE `{$col}` IS NOT NULL",
                        [$offsetHours]
                    );
                }
            }
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn("Dry-run selesai ({$totalCols} kolom). Tambahkan --force untuk benar-benar menggeser.");
            return self::SUCCESS;
        }

        @file_put_contents($marker, now()->toIso8601String() . " offset={$offsetHours}h tz={$tz}\n");
        $this->newLine();
        $this->info("Selesai. {$totalCols} kolom digeser {$offsetHours} jam.");

        return self::SUCCESS;
    }
}
