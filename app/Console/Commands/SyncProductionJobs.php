<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Erp\ErpProductionClient;
use App\Services\Production\ProductionJobSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Jaring pengaman: tarik antrean produksi ERP per outlet, buat job yang belum ada,
 * tutup job yang item ERP-nya sudah tidak in_production. Idempoten dengan webhook.
 */
class SyncProductionJobs extends Command
{
    protected $signature = 'production:sync-jobs {--outlet= : Batasi ke satu outlet ERP}';

    protected $description = 'Sinkronkan broadcast job produksi dari antrean ERP (jaring pengaman webhook).';

    public function handle(ErpProductionClient $erp, ProductionJobSyncService $sync): int
    {
        if (!filter_var(config('services.erp.production_jobs_enabled', true), FILTER_VALIDATE_BOOL)) {
            $this->warn('PRODUCTION_JOB_SYNC_ENABLED=false — dilewati.');
            return self::SUCCESS;
        }

        if (!$erp->isConfigured()) {
            $this->warn('Integrasi ERP belum dikonfigurasi — dilewati.');
            return self::SUCCESS;
        }

        $outletOption = $this->option('outlet');
        $outletIds = $outletOption
            ? [(int) $outletOption]
            : User::query()
                ->whereNotNull('erp_outlet_id')
                ->where('can_close_production', true)
                ->distinct()
                ->pluck('erp_outlet_id')
                ->map(fn ($v) => (int) $v)
                ->filter()
                ->all();

        if ($outletIds === []) {
            $this->info('Tidak ada outlet produksi aktif.');
            return self::SUCCESS;
        }

        $totalCreated = 0;
        $totalClosed = 0;

        foreach ($outletIds as $outletId) {
            try {
                $page = 1;
                $liveIds = [];

                do {
                    $result = $erp->listItems([
                        'outlet_id' => $outletId,
                        'status' => 'in_production',
                        'page' => $page,
                        'per_page' => 200,
                    ]);

                    foreach ($result['data'] as $item) {
                        $liveIds[] = (int) ($item['erp_item_id'] ?? 0);
                        $before = \App\Models\JobTodo::query()->where('erp_item_id', (int) ($item['erp_item_id'] ?? 0))->exists();
                        $sync->upsertFromErpItem($item);
                        if (!$before) {
                            $totalCreated++;
                        }
                    }

                    $lastPage = (int) ($result['meta']['last_page'] ?? 1);
                    $page++;
                } while ($page <= $lastPage && $page <= 20);

                $closed = $sync->closeStaleForOutlet($outletId, array_values(array_filter($liveIds)));
                $totalClosed += $closed;

                $this->info("Outlet {$outletId}: " . count($liveIds) . " item live, {$closed} job ditutup.");
            } catch (\Throwable $e) {
                Log::warning('[production] sync-jobs outlet gagal', [
                    'outlet_id' => $outletId,
                    'error' => $e->getMessage(),
                ]);
                $this->error("Outlet {$outletId}: {$e->getMessage()}");
            }
        }

        $this->info("Selesai. Dibuat: {$totalCreated}, ditutup: {$totalClosed}.");

        return self::SUCCESS;
    }
}
