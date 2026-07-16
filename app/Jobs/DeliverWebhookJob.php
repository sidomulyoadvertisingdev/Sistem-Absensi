<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class DeliverWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public string $url,
        public string $secret,
        public array $payload
    ) {
    }

    public function handle(): void
    {
        $body = json_encode($this->payload);

        $signature = 'sha256=' . hash_hmac('sha256', $body, $this->secret);

        $response = Http::timeout(10)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'X-Integration-Signature' => $signature,
                'User-Agent' => 'SidoMulyo-Webhook',
            ])
            ->post($this->url, $this->payload);

        if ($response->failed()) {
            throw new \RuntimeException(
                'Webhook delivery failed: HTTP ' . $response->status()
            );
        }
    }
}
