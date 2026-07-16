<?php

namespace App\Console\Commands;

use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanupOldAbsensiFoto extends Command
{
    protected $signature = 'absensi:cleanup-old-foto';

    protected $description = 'Hapus foto absensi bulan lalu dari server (yang sudah ter-backup ke Google Drive)';

    public function handle(): int
    {
        $cutoffDate = Carbon::now()->startOfMonth();

        $this->info("Menghapus foto absensi sebelum {$cutoffDate->format('Y-m-d')}...");

        $records = Absensi::whereNotNull('foto')
            ->where('foto_backed_up', true)
            ->where('tanggal', '<', $cutoffDate)
            ->get();

        if ($records->isEmpty()) {
            $this->info('Tidak ada foto yang perlu dihapus.');
            return self::SUCCESS;
        }

        $deleteCount = 0;
        $skipCount = 0;

        foreach ($records as $absensi) {
            $localPath = Storage::disk('public')->path($absensi->foto);

            if (file_exists($localPath)) {
                unlink($localPath);
                $this->line("  🗑  " . basename($absensi->foto));
            }

            $absensi->update(['foto' => null]);
            $deleteCount++;
        }

        $unbackedUp = Absensi::whereNotNull('foto')
            ->where('foto_backed_up', false)
            ->where('tanggal', '<', $cutoffDate)
            ->count();

        if ($unbackedUp > 0) {
            $this->warn("⚠  {$unbackedUp} foto belum ter-backup dan TIDAK dihapus.");
        }

        $this->info("Cleanup selesai. {$deleteCount} foto dihapus dari server.");

        Log::info('Cleanup foto absensi selesai', [
            'cutoff_date' => $cutoffDate->toDateString(),
            'deleted' => $deleteCount,
            'unbacked_up_skipped' => $unbackedUp,
        ]);

        return self::SUCCESS;
    }
}
