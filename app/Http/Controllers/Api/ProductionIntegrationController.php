<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Production\ProductionJobSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Ingest webhook ERP -> Absensi: item produksi masuk / keluar antrean.
 * Auth: Sanctum (panel admin) + ability token `integration.production.items.write`.
 * Body: { event, items: [ { erp_item_id, erp_order_id, invoice_no, outlet_id, ... } ] }
 */
class ProductionIntegrationController extends Controller
{
    public function __construct(
        private readonly ProductionJobSyncService $sync,
    ) {
    }

    public function store(Request $request): JsonResponse
    {
        if ($response = $this->ensureAuthorized($request, 'integration.production.items.write')) {
            return $response;
        }

        if (!filter_var(config('services.erp.production_jobs_enabled', true), FILTER_VALIDATE_BOOL)) {
            return response()->json(['status' => 'disabled', 'message' => 'Fitur produksi belum diaktifkan.'], 503);
        }

        $data = $request->validate([
            'event' => ['nullable', 'string', 'in:entered_production,completed,cancelled'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.erp_item_id' => ['required', 'integer', 'min:1'],
            'items.*.erp_order_id' => ['nullable', 'integer'],
            'items.*.invoice_no' => ['nullable', 'string', 'max:100'],
            'items.*.outlet_id' => ['nullable', 'integer'],
            'items.*.customer_name' => ['nullable', 'string', 'max:150'],
            'items.*.customer_phone' => ['nullable', 'string', 'max:40'],
            'items.*.product_name' => ['nullable', 'string', 'max:190'],
            'items.*.qty' => ['nullable'],
            'items.*.deadline_at' => ['nullable', 'string', 'max:40'],
            'items.*.spec_summary' => ['nullable', 'string', 'max:2000'],
            'items.*.production_status' => ['nullable', 'string', 'max:30'],
        ]);

        $event = (string) ($data['event'] ?? 'entered_production');
        $result = $this->sync->handleErpEvent($event, $data['items']);

        return response()->json([
            'status' => 'ok',
            'event' => $event,
            'result' => $result,
        ]);
    }

    private function ensureAuthorized(Request $request, string $ability): ?JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->isPanelAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        $token = $user->currentAccessToken();
        if ($request->bearerToken() && $token && !$token->can($ability)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak memiliki izin untuk endpoint ini.',
            ], 403);
        }

        return null;
    }
}
