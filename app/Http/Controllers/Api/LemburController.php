<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lembur;
use Carbon\Carbon;

class LemburController extends Controller
{
    /**
     * ===============================
     * LIST LEMBUR USER LOGIN
     * ===============================
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $data = Lembur::where('user_id', $user->id)
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * ===============================
     * AJUKAN LEMBUR (USER)
     * ===============================
     * - jam_mulai otomatis (jam request)
     * - jam_selesai NULL
     * - status = requested
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal'    => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        $now = Carbon::now();

        $lembur = Lembur::create([
            'user_id'     => $request->user()->id,
            'tanggal'     => $request->tanggal,
            'jam_mulai'   => $now->format('H:i'),
            'jam_selesai' => null,
            'keterangan'  => $request->keterangan,
            'status'      => 'requested',
        ]);

        return response()->json([
            'message' => 'Pengajuan lembur berhasil dikirim',
            'data'    => $lembur,
        ]);
    }

    /**
     * ===============================
     * SELESAI LEMBUR (USER)
     * ===============================
     * - hanya jika status = approved
     * - jam_selesai otomatis
     * - status = finished
     */
    public function finish(Request $request, Lembur $lembur)
    {
        // pastikan lembur milik user
        if ($lembur->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Akses ditolak'
            ], 403);
        }

        // hanya bisa selesai jika sudah di-approve admin
        if ($lembur->status !== 'approved') {
            return response()->json([
                'message' => 'Lembur belum disetujui admin'
            ], 422);
        }

        $lembur->update([
            'jam_selesai' => Carbon::now()->format('H:i'),
            'status'      => 'finished',
        ]);

        return response()->json([
            'message' => 'Lembur selesai dan akan dihitung ke gaji',
            'data'    => $lembur,
        ]);
    }
}
