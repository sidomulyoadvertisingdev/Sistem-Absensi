<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Submission;
use App\Models\SubmissionType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubmissionApiController extends Controller
{
    public function types(Request $request)
    {
        $typesQuery = SubmissionType::where('aktif', true)
            ->orderBy('nama')
            ->select([
                'id',
                'kode',
                'nama',
                'deskripsi',
                'butuh_alasan',
                'butuh_lampiran',
                'is_izin_pulang_awal',
            ]);

        // sidomulyo-app saat ini belum mendukung upload lampiran pada submission.
        if (strtolower((string) $request->header('X-Client-Type')) === 'mobile') {
            $typesQuery->where('butuh_lampiran', false);
        }

        $types = $typesQuery->get();

        return response()->json([
            'status' => true,
            'data' => $types,
        ]);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User tidak terautentikasi',
            ], 401);
        }

        $data = Submission::with('type')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User tidak terautentikasi',
            ], 401);
        }

        $request->validate([
            'submission_type_id' => 'required|exists:submission_types,id',
            'alasan' => 'nullable|string',
            'lampiran' => 'nullable|file|max:2048',
        ]);

        $type = SubmissionType::where('id', $request->submission_type_id)
            ->where('aktif', true)
            ->firstOrFail();

        if ($type->butuh_alasan && empty($request->alasan)) {
            return response()->json([
                'status' => false,
                'message' => 'Alasan wajib diisi',
            ], 422);
        }

        if ($type->butuh_lampiran && !$request->hasFile('lampiran')) {
            return response()->json([
                'status' => false,
                'message' => 'Lampiran wajib diunggah',
            ], 422);
        }

        if ($type->is_izin_pulang_awal) {
            $today = Carbon::today()->toDateString();

            $absensiToday = Absensi::where('user_id', $user->id)
                ->where('tanggal', $today)
                ->first();

            if (!$absensiToday || empty($absensiToday->jam_masuk)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pengajuan ini hanya bisa dibuat setelah absen masuk.',
                ], 422);
            }

            if (!empty($absensiToday->jam_pulang)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Absensi hari ini sudah ditutup, pengajuan tidak diperlukan.',
                ], 422);
            }

            $pendingToday = Submission::where('user_id', $user->id)
                ->where('submission_type_id', $type->id)
                ->whereDate('created_at', $today)
                ->where('status', 'pending')
                ->exists();

            if ($pendingToday) {
                return response()->json([
                    'status' => false,
                    'message' => 'Anda sudah mengajukan izin pulang awal hari ini.',
                ], 422);
            }
        }

        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')
                ->store('submission-lampiran', 'public');
        }

        $submission = Submission::create([
            'user_id' => $user->id,
            'submission_type_id' => $type->id,
            'kode' => $type->kode,
            'nama' => $type->nama,
            'alasan' => $request->alasan,
            'lampiran' => $lampiranPath,
            'status' => 'pending',
        ]);

        $submission->load('type');

        return response()->json([
            'status' => true,
            'message' => 'Pengajuan berhasil dikirim',
            'data' => $submission,
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User tidak terautentikasi',
            ], 401);
        }

        $submission = Submission::with('type')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data' => $submission,
        ]);
    }
}
