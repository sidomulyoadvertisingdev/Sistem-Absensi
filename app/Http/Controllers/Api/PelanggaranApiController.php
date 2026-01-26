<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PelanggaranLog;
use Illuminate\Http\Request;

class PelanggaranApiController extends Controller
{
    /**
     * ===============================
     * LIST PELANGGARAN USER LOGIN
     * ===============================
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $data = PelanggaranLog::where('user_id', $user->id)
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id'                => $item->id,
                    'tanggal'           => $item->tanggal,
                    'jenis_pelanggaran' => $item->jenis_pelanggaran,
                    'kategori'          => $item->kategori,
                    'lokasi'            => $item->lokasi,
                    'tindakan'          => $item->tindakan,
                ];
            });

        return response()->json([
            'data' => $data
        ], 200);
    }

    /**
     * ===============================
     * DETAIL PELANGGARAN
     * ===============================
     */
    public function show($id, Request $request)
    {
        $user = $request->user();

        $pelanggaran = PelanggaranLog::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return response()->json([
            'data' => [
                'id'                => $pelanggaran->id,
                'tanggal'           => $pelanggaran->tanggal,
                'jenis_pelanggaran' => $pelanggaran->jenis_pelanggaran,
                'kategori'          => $pelanggaran->kategori,
                'lokasi'            => $pelanggaran->lokasi,
                'kronologi'         => $pelanggaran->kronologi,
                'tindakan'          => $pelanggaran->tindakan,
                'bukti'             => $pelanggaran->bukti
                    ? asset('storage/' . $pelanggaran->bukti)
                    : null,
            ]
        ], 200);
    }
}
