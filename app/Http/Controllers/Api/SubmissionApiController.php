<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\SubmissionType;
use Illuminate\Http\Request;

class SubmissionApiController extends Controller
{
    /**
     * =====================================================
     * 🔹 LIST JENIS PENGAJUAN AKTIF
     * =====================================================
     */
    public function types()
    {
        $types = SubmissionType::where('aktif', true)
            ->orderBy('nama')
            ->get([
                'id',
                'kode',
                'nama',
                'deskripsi',
                'butuh_alasan',
                'butuh_lampiran',
            ]);

        return response()->json([
            'status' => true,
            'data' => $types,
        ]);
    }

    /**
     * =====================================================
     * 🔹 LIST PENGAJUAN USER LOGIN
     * =====================================================
     */
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

    /**
     * =====================================================
     * 🔹 KIRIM PENGAJUAN BARU
     * =====================================================
     */
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

        // Ambil jenis pengajuan aktif
        $type = SubmissionType::where('id', $request->submission_type_id)
            ->where('aktif', true)
            ->firstOrFail();

        // 🔒 Validasi aturan dari admin
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

        // 📎 Upload lampiran
        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')
                ->store('submission-lampiran', 'public');
        }

        // 💾 Simpan submission (snapshot)
        $submission = Submission::create([
            'user_id' => $user->id,
            'submission_type_id' => $type->id,
            'kode' => $type->kode,
            'nama' => $type->nama,
            'alasan' => $request->alasan,
            'lampiran' => $lampiranPath,
            'status' => 'pending',
        ]);

        /**
         * 🔥 PENTING
         * Load relasi supaya frontend TIDAK error
         */
        $submission->load('type');

        return response()->json([
            'status' => true,
            'message' => 'Pengajuan berhasil dikirim',
            'data' => $submission,
        ], 201);
    }

    /**
     * =====================================================
     * 🔹 DETAIL PENGAJUAN USER LOGIN
     * =====================================================
     */
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
