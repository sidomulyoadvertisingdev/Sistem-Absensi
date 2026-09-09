<?php

namespace App\Services\Production;

use App\Events\JobTodoCreated;
use App\Models\JobTodo;
use App\Models\User;
use App\Services\PushNotifier;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Menjaga tabel job_todos (type=production) tetap sinkron dengan antrean produksi ERP.
 * Dipakai oleh webhook ingest (instan) dan command poll (jaring pengaman).
 */
class ProductionJobSyncService
{
    public function __construct(
        private readonly PushNotifier $push,
    ) {
    }

    /**
     * Buat / perbarui satu production job dari payload item ERP.
     *
     * @param  array<string, mixed>  $item
     */
    public function upsertFromErpItem(array $item): ?JobTodo
    {
        $erpItemId = (int) ($item['erp_item_id'] ?? 0);
        if ($erpItemId <= 0) {
            return null;
        }

        $productionStatus = strtolower((string) ($item['production_status'] ?? 'in_production'));
        $existing = JobTodo::query()->where('erp_item_id', $erpItemId)->first();

        $display = [
            'erp_order_id' => (int) ($item['erp_order_id'] ?? 0) ?: null,
            'invoice_no' => $this->str($item['invoice_no'] ?? null),
            'outlet_id' => (int) ($item['outlet_id'] ?? 0) ?: null,
            'customer_name' => $this->str($item['customer_name'] ?? null),
            'customer_phone' => $this->str($item['customer_phone'] ?? null),
            'product_name' => $this->str($item['product_name'] ?? null) ?: 'Item Produksi',
            'qty' => $this->str($item['qty'] ?? null),
            'deadline_at' => $this->date($item['deadline_at'] ?? null),
            'spec_summary' => $this->str($item['spec_summary'] ?? null),
            'production_status' => $productionStatus ?: 'in_production',
        ];

        if ($existing) {
            // Jangan sentuh job yang sudah diselesaikan/ditutup lokal.
            if (in_array($existing->status, ['done', 'closed'], true)) {
                return $existing;
            }

            if ($productionStatus === 'completed') {
                // Item sudah selesai di ERP (mis. lewat POS) — tutup broadcast job.
                $existing->update(array_merge($display, ['status' => 'closed']));
                return $existing;
            }

            $existing->update(array_merge($display, [
                'title' => $this->buildTitle($display),
            ]));

            return $existing;
        }

        if ($productionStatus !== 'in_production') {
            return null; // tidak membuat job untuk item yang bukan in_production
        }

        $job = JobTodo::query()->create(array_merge($display, [
            'type' => JobTodo::TYPE_PRODUCTION,
            'source' => 'pos',
            'erp_item_id' => $erpItemId,
            'title' => $this->buildTitle($display),
            'description' => $this->buildDescription($display),
            'bonus' => 0,
            'broadcast' => true,
            'status' => 'open',
        ]));

        $this->notifyEligibleHelpers($job);

        return $job;
    }

    /**
     * Tutup production job yang item ERP-nya tidak lagi berada di antrean in_production.
     *
     * @param  array<int, int>  $liveErpItemIds
     */
    public function closeStaleForOutlet(int $outletId, array $liveErpItemIds): int
    {
        $query = JobTodo::query()
            ->production()
            ->where('outlet_id', $outletId)
            ->whereIn('status', ['open', 'in_progress']);

        if ($liveErpItemIds !== []) {
            $query->whereNotIn('erp_item_id', $liveErpItemIds);
        }

        $stale = $query->get();
        foreach ($stale as $job) {
            $job->update(['status' => 'closed', 'production_status' => 'completed']);
        }

        return $stale->count();
    }

    /**
     * Proses payload event webhook dari ERP.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array{processed:int, created:int, closed:int}
     */
    public function handleErpEvent(string $event, array $items): array
    {
        $created = 0;
        $closed = 0;

        foreach ($items as $item) {
            $erpItemId = (int) ($item['erp_item_id'] ?? 0);
            if ($erpItemId <= 0) {
                continue;
            }

            if (in_array($event, ['completed', 'cancelled'], true)) {
                $job = JobTodo::query()->where('erp_item_id', $erpItemId)->first();
                if ($job && !in_array($job->status, ['done', 'closed'], true)) {
                    $job->update([
                        'status' => 'closed',
                        'production_status' => $event === 'completed' ? 'completed' : ($job->production_status ?? 'in_production'),
                    ]);
                    $closed++;
                }
                continue;
            }

            // entered_production (default)
            $before = JobTodo::query()->where('erp_item_id', $erpItemId)->exists();
            $job = $this->upsertFromErpItem($item);
            if ($job && !$before) {
                $created++;
            }
        }

        return ['processed' => count($items), 'created' => $created, 'closed' => $closed];
    }

    private function notifyEligibleHelpers(JobTodo $job): void
    {
        try {
            event(new JobTodoCreated($job));
        } catch (\Throwable $e) {
            Log::warning('[production] broadcast JobTodoCreated gagal', ['error' => $e->getMessage()]);
        }

        $helperIds = User::query()
            ->where('role', User::ROLE_KARYAWAN)
            ->where('can_close_production', true)
            ->when($job->outlet_id, fn ($q) => $q->where('erp_outlet_id', $job->outlet_id))
            ->pluck('id')
            ->all();

        if ($helperIds === []) {
            return;
        }

        $this->push->send(
            $helperIds,
            'Orderan produksi baru',
            trim(($job->product_name ?: 'Item produksi') . ' — ' . ($job->customer_name ?: 'Pelanggan')),
            ['screen' => '/production', 'job_id' => $job->id, 'type' => 'production_broadcast'],
        );
    }

    /** @param array<string, mixed> $d */
    private function buildTitle(array $d): string
    {
        $product = (string) ($d['product_name'] ?? 'Item Produksi');
        $customer = trim((string) ($d['customer_name'] ?? ''));

        return $customer !== '' ? "{$product} — {$customer}" : $product;
    }

    /** @param array<string, mixed> $d */
    private function buildDescription(array $d): string
    {
        return trim(implode("\n", array_filter([
            $d['invoice_no'] ? 'Nota: ' . $d['invoice_no'] : null,
            $d['qty'] ? 'Qty: ' . $d['qty'] : null,
            $d['deadline_at'] ? 'Deadline: ' . Carbon::parse($d['deadline_at'])->format('d M Y H:i') : null,
            $d['spec_summary'] ?: null,
        ])));
    }

    private function str(mixed $v): ?string
    {
        $s = trim((string) ($v ?? ''));
        return $s === '' ? null : $s;
    }

    private function date(mixed $v): ?string
    {
        if (empty($v)) {
            return null;
        }
        try {
            return Carbon::parse($v)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }
}
