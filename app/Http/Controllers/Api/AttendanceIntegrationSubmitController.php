<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AttendanceRecorder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceIntegrationSubmitController extends Controller
{
    public function __construct(
        protected AttendanceRecorder $recorder
    ) {
    }

    public function submit(Request $request): JsonResponse
    {
        if ($response = $this->ensureAuthorized($request, 'integration.attendance.submit')) {
            return $response;
        }

        $rules = [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'aksi' => ['required', 'in:masuk,istirahat_mulai,istirahat_selesai,pulang'],
            'jam' => ['required'],
            'tanggal' => ['nullable', 'date'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];

        // Mode batch: jika ada 'records' (array) proses sebagai banyak record.
        if ($request->has('records') && is_array($request->input('records'))) {
            $records = $request->input('records');
            $errors = [];

            foreach ($records as $index => $item) {
                $itemValidator = Validator::make((array) $item, $rules);

                if ($itemValidator->fails()) {
                    $errors[$index] = $itemValidator->errors()->toArray();
                    continue;
                }

                $validated = $itemValidator->validated();
                $result = $this->persist(
                    (int) $validated['user_id'],
                    $validated['aksi'],
                    $validated['jam'],
                    $validated['tanggal'] ?? null,
                    null
                );

                if ($result['error']) {
                    $errors[$index] = ['user_id' => [$result['error']]];
                }
            }

            return response()->json([
                'status' => empty($errors) ? 'ok' : 'partial',
                'message' => empty($errors)
                    ? 'Semua absensi berhasil disimpan.'
                    : 'Sebagian absensi gagal disimpan.',
                'processed' => count($records) - count($errors),
                'failed' => count($errors),
                'errors' => $errors,
            ]);
        }

        // Mode single.
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $result = $this->persist(
            (int) $validated['user_id'],
            $validated['aksi'],
            $validated['jam'],
            $validated['tanggal'] ?? null,
            $request->file('foto')
        );

        if ($result['error']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['error'],
            ], 422);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Absensi berhasil disimpan.',
            'data' => $result['absensi'],
        ]);
    }

    private function persist(int $userId, string $aksi, string $jam, ?string $tanggal, ?\Illuminate\Http\UploadedFile $foto): array
    {
        $user = User::find($userId);

        if (!$user) {
            return ['error' => 'User tidak ditemukan.', 'absensi' => null];
        }

        $tanggal = $tanggal ?? now()->toDateString();

        try {
            $absensi = $this->recorder->record(
                user: $user,
                tanggal: $tanggal,
                aksi: $aksi,
                jam: $jam,
                foto: $foto,
                requireFoto: false
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ['error' => collect($e->errors())->first()[0] ?? 'Validasi gagal.', 'absensi' => null];
        }

        return ['error' => null, 'absensi' => $absensi];
    }

    private function ensureAuthorized(Request $request, string $ability): ?JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->isPanelAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak.',
            ], 403);
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
