<?php

namespace App\Services;

use App\Models\PushToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pengirim push notification Expo (https://exp.host/--/api/v2/push/send).
 * Fire-and-forget: kegagalan tidak boleh menggagalkan alur pemanggil.
 */
class PushNotifier
{
    /**
     * @param  array<int, int>  $userIds
     * @param  array<string, mixed>  $data
     */
    public function send(array $userIds, string $title, string $body, array $data = []): int
    {
        $userIds = array_values(array_unique(array_map('intval', $userIds)));
        if ($userIds === []) {
            return 0;
        }

        $tokens = PushToken::query()
            ->whereIn('user_id', $userIds)
            ->pluck('token')
            ->unique()
            ->values();

        if ($tokens->isEmpty()) {
            return 0;
        }

        $sent = 0;

        foreach ($tokens->chunk(100) as $chunk) {
            $payload = $chunk->map(fn ($token) => [
                'to' => $token,
                'sound' => 'default',
                'title' => $title,
                'body' => $body,
                'channelId' => 'jobs',
                'data' => $data === [] ? new \stdClass() : $data,
            ])->values()->all();

            try {
                $response = Http::timeout(15)
                    ->acceptJson()
                    ->asJson()
                    ->post('https://exp.host/--/api/v2/push/send', $payload);

                if ($response->successful()) {
                    foreach ($response->json('data', []) as $receipt) {
                        if (($receipt['status'] ?? '') === 'ok') {
                            $sent++;
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('[push] gagal kirim Expo push', ['error' => $e->getMessage()]);
            }
        }

        return $sent;
    }
}
