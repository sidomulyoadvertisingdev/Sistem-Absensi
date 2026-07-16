<?php

namespace App\Listeners;

use App\Events\AttendanceRecorded;
use App\Jobs\DeliverWebhookJob;
use App\Models\IntegrationWebhook;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

#[ListensTo(AttendanceRecorded::class)]
class SendAttendanceWebhook implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(AttendanceRecorded $event): void
    {
        $webhooks = IntegrationWebhook::query()
            ->where('is_active', true)
            ->whereNotNull('webhook_url')
            ->get();

        foreach ($webhooks as $webhook) {
            if (!$webhook->subscribes('attendance')) {
                continue;
            }

            try {
                DeliverWebhookJob::dispatch(
                    $webhook->webhook_url,
                    $webhook->secret,
                    $this->buildPayload($event)
                );

                $webhook->update(['last_error' => null]);
            } catch (\Throwable $e) {
                $webhook->update(['last_error' => $e->getMessage()]);

                Log::error('Gagal menjadwalkan webhook absensi', [
                    'webhook_id' => $webhook->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function buildPayload(AttendanceRecorded $event): array
    {
        $absensi = $event->absensi;
        $user = $event->user;

        return [
            'event' => 'attendance.recorded',
            'occurred_at' => now()->toIso8601String(),
            'data' => [
                'user_id' => $user->id,
                'nik' => $user->nik,
                'name' => $user->name,
                'jabatan' => $user->jabatan,
                'penempatan' => $user->penempatan,
                'tanggal' => $absensi->tanggal?->toDateString(),
                'aksi' => $event->aksi,
                'jam_masuk' => $absensi->jam_masuk,
                'istirahat_mulai' => $absensi->istirahat_mulai,
                'istirahat_selesai' => $absensi->istirahat_selesai,
                'jam_pulang' => $absensi->jam_pulang,
                'status' => $absensi->status,
                'menit_terlambat' => $absensi->menit_terlambat,
            ],
        ];
    }
}
