<?php

namespace App\Services\Erp;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Klien ke External API ERPSIDOMULYO untuk fitur produksi.
 * Endpoint: /api/v1/external/production/items[/{id}/complete]
 * Auth   : header X-Api-Key / X-Api-Secret (scope: production, allowed_outlets)
 */
class ErpProductionClient
{
    public function isConfigured(): bool
    {
        return (bool) config('services.erp.base_url')
            && (bool) config('services.erp.api_key')
            && (bool) config('services.erp.api_secret');
    }

    /**
     * @param  array{outlet_id:int, status?:string, search?:string, page?:int, per_page?:int}  $filters
     * @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>}
     */
    public function listItems(array $filters): array
    {
        $response = $this->client()->get($this->url('production/items'), array_filter([
            'outlet_id' => $filters['outlet_id'] ?? null,
            'status' => $filters['status'] ?? null,
            'search' => $filters['search'] ?? null,
            'page' => $filters['page'] ?? null,
            'per_page' => $filters['per_page'] ?? null,
        ], fn ($v) => $v !== null && $v !== ''));

        if (!$response->successful()) {
            throw new RuntimeException(
                'ERP listItems HTTP ' . $response->status() . ': ' . $this->shortBody($response->body())
            );
        }

        $json = $response->json();

        return [
            'data' => is_array($json['data'] ?? null) ? $json['data'] : [],
            'meta' => is_array($json['meta'] ?? null) ? $json['meta'] : [],
        ];
    }

    /**
     * @param  array{outlet_id:int, note?:string, actor:array<string, mixed>}  $payload
     * @return array<string, mixed>
     */
    public function completeItem(int $erpItemId, array $payload): array
    {
        $response = $this->client()->post(
            $this->url('production/items/' . $erpItemId . '/complete'),
            $payload
        );

        $json = $response->json();

        if (!$response->successful()) {
            $message = is_array($json) ? (string) ($json['message'] ?? '') : '';
            throw new RuntimeException(
                $message !== ''
                    ? $message
                    : 'ERP completeItem HTTP ' . $response->status() . ': ' . $this->shortBody($response->body()),
                $response->status()
            );
        }

        return is_array($json) ? $json : [];
    }

    private function client()
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Integrasi ERP belum dikonfigurasi (ERP_BASE_URL / ERP_PARTNER_API_KEY / ERP_PARTNER_API_SECRET).');
        }

        return Http::acceptJson()
            ->timeout(max(3, (int) config('services.erp.timeout', 15)))
            ->retry(2, 400, throw: false)
            ->withHeaders([
                'X-Api-Key' => (string) config('services.erp.api_key'),
                'X-Api-Secret' => (string) config('services.erp.api_secret'),
            ]);
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.erp.base_url'), '/') . '/v1/external/' . ltrim($path, '/');
    }

    private function shortBody(?string $body): string
    {
        return mb_substr((string) $body, 0, 300);
    }
}
