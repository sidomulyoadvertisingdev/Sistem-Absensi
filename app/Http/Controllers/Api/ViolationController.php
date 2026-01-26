<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PelanggaranLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ViolationController extends Controller
{
    /**
     * ===============================
     * LIST PELANGGARAN USER LOGIN
     * ===============================
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $pelanggaran = PelanggaranLog::where('user_id', $user->id)
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(function ($p) {
                return [
                    'id'                 => $p->id,
                    'tanggal'            => $p->tanggal->format('d M Y'),
                    'kode_pelanggaran'   => $p->kode_pelanggaran,
                    'jenis_pelanggaran'  => $p->jenis_pelanggaran,
                    'kategori'           => $p->kategori,
                    'lokasi'             => $p->lokasi,
                    'tindakan'           => $p->tindakan,
                    'has_sp'             => !empty($p->sp_file),
                ];
            });

        return response()->json([
            'data' => $pelanggaran
        ]);
    }

    /**
     * ===============================
     * DETAIL PELANGGARAN
     * ===============================
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $p = PelanggaranLog::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return response()->json([
            'data' => [
                'id'                => $p->id,
                'tanggal'           => $p->tanggal->format('d M Y'),
                'kode_pelanggaran'  => $p->kode_pelanggaran,
                'jenis_pelanggaran' => $p->jenis_pelanggaran,
                'kategori'          => $p->kategori,
                'jabatan'           => $p->jabatan,
                'lokasi'            => $p->lokasi,
                'kronologi'         => $p->kronologi,
                'tindakan'          => $p->tindakan,
                'catatan'           => $p->catatan,
                'bukti'             => $p->bukti
                    ? asset('storage/' . $p->bukti)
                    : null,
                'sp_file'           => $p->sp_file
                    ? asset('storage/' . $p->sp_file)
                    : null,
                'penanggung_jawab'  => $p->penanggung_jawab,
            ]
        ]);
    }

    /**
     * ===============================
     * DOWNLOAD SP
     * ===============================
     */
    public function downloadSp(Request $request, $id)
    {
        $user = $request->user();

        $p = PelanggaranLog::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (!$p->sp_file || !Storage::disk('public')->exists($p->sp_file)) {
            abort(404, 'File SP tidak ditemukan');
        }

        return response()->download(
            storage_path('app/public/' . $p->sp_file),
            'SP-' . $p->kode_pelanggaran . '.pdf'
        );
    }
}
