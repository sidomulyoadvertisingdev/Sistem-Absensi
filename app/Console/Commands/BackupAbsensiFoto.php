<?php

namespace App\Console\Commands;

use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BackupAbsensiFoto extends Command
{
    protected $signature = 'absensi:backup-foto';

    protected $description = 'Backup foto absensi yang belum ter-backup ke Google Drive';

    public function handle(): int
    {
        $record = Absensi::whereNotNull('foto')
            ->where('foto_backed_up', false)
            ->orderBy('tanggal')
            ->first();

        if (! $record) {
            $this->info('Tidak ada foto yang perlu di-backup.');
            return self::SUCCESS;
        }

        $this->info('Memulai backup foto absensi ke Google Drive...');

        $successCount = 0;
        $failCount = 0;

        $records = Absensi::whereNotNull('foto')
            ->where('foto_backed_up', false)
            ->orderBy('tanggal')
            ->get();

        foreach ($records as $absensi) {
            $localPath = Storage::disk('public')->path($absensi->foto);

            if (! file_exists($localPath)) {
                $this->warn("File tidak ditemukan: {$absensi->foto} (ID: {$absensi->id})");
                $failCount++;
                continue;
            }

            $filename = basename($absensi->foto);
            $monthFolder = $absensi->tanggal->format('Y-m');
            $googleDrivePath = "Foto-Absensi/{$monthFolder}/{$filename}";

            try {
                $fileContents = file_get_contents($localPath);

                Storage::disk('google')->put($googleDrivePath, $fileContents);

                $absensi->update(['foto_backed_up' => true]);

                $successCount++;
                $this->line("  ✓ {$filename} -> {$googleDrivePath}");
            } catch (\Exception $e) {
                $failCount++;
                $this->error("  ✗ {$filename} gagal: {$e->getMessage()}");
                Log::error("Backup foto absensi gagal", [
                    'absensi_id' => $absensi->id,
                    'file' => $absensi->foto,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Backup selesai. Berhasil: {$successCount}, Gagal: {$failCount}");

        if ($failCount > 0) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
