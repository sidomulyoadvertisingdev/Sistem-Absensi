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
     * - foto_mulai WAJIB (bukti pengajuan lembur)
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal'    => 'required|date',
            'keterangan' => 'nullable|string',
            'foto_mulai' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $now = Carbon::now();

        $fotoPath = $request->file('foto_mulai')->store('lembur', 'public');

        $lembur = Lembur::create([
            'user_id'     => $request->user()->id,
            'tanggal'     => $request->tanggal,
            'jam_mulai'   => $now->format('H:i'),
            'jam_selesai' => null,
            'foto_mulai'  => $fotoPath,
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
     * - bisa langsung diselesaikan TANPA menunggu approval admin
     *   (approval admin hanya menentukan kapan nominal masuk ke gaji,
     *   bukan syarat untuk menyelesaikan pekerjaan lembur)
     * - jam_selesai otomatis
     * - status = finished
     * - foto_selesai WAJIB (bukti lembur selesai dikerjakan)
     */
    public function finish(Request $request, Lembur $lembur)
    {
        // pastikan lembur milik user
        if ($lembur->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Akses ditolak'
            ], 403);
        }

        // hanya bisa selesai jika masih berstatus requested (belum selesai/ditolak)
        if ($lembur->status !== 'requested') {
            return response()->json([
                'message' => 'Lembur ini sudah selesai atau tidak bisa diselesaikan lagi'
            ], 422);
        }

        $request->validate([
            'foto_selesai' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $fotoPath = $request->file('foto_selesai')->store('lembur', 'public');

        $lembur->update([
            'jam_selesai'  => Carbon::now()->format('H:i'),
            'foto_selesai' => $fotoPath,
            'status'       => 'finished',
        ]);

        return response()->json([
            'message' => 'Lembur selesai dan akan dihitung ke gaji',
            'data'    => $lembur,
        ]);
    }
}
