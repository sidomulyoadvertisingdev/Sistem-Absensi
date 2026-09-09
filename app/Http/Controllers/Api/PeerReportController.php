<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PeerReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PeerReportController extends Controller
{
    /**
     * Karyawan membuat laporan terhadap rekan kerja.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'reported_user_id' => ['required', 'integer', 'exists:users,id'],
            'kategori' => ['required', 'string', 'in:' . implode(',', PeerReport::CATEGORIES)],
            'lokasi' => ['nullable', 'string', 'max:150'],
            'kronologi' => ['required', 'string', 'min:5', 'max:4000'],
            'bukti' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
        ], [], ['reported_user_id' => 'karyawan yang dilaporkan']);

        if ((int) $data['reported_user_id'] === (int) $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Anda tidak bisa melaporkan diri sendiri.',
            ], 422);
        }

        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $buktiPath = $request->file('bukti')->store('peer-reports', 'public');
        }

        PeerReport::create([
            'reporter_id' => $user->id,
            'reported_user_id' => (int) $data['reported_user_id'],
            'kategori' => $data['kategori'],
            'lokasi' => $data['lokasi'] ?? null,
            'kronologi' => $data['kronologi'],
            'bukti_path' => $buktiPath,
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Laporan terkirim. Management akan memverifikasi sebelum dinyatakan valid.',
        ], 201);
    }

    /**
     * Laporan yang SUDAH DISETUJUI management terhadap user yang login.
     * Identitas pelapor TIDAK disertakan.
     */
    public function aboutMe(Request $request)
    {
        $user = $request->user();

        $rows = PeerReport::query()
            ->approved()
            ->where('reported_user_id', $user->id)
            ->orderByDesc('reviewed_at')
            ->get(['id', 'kategori', 'lokasi', 'kronologi', 'catatan_admin', 'reviewed_at', 'created_at']);

        return response()->json([
            'status' => true,
            'count' => $rows->count(),
            'data' => $rows->map(fn (PeerReport $r) => [
                'id' => $r->id,
                'kategori' => $r->kategori,
                'lokasi' => $r->lokasi,
                'kronologi' => $r->kronologi,
                'catatan_admin' => $r->catatan_admin,
                'disetujui_pada' => optional($r->reviewed_at)->toIso8601String(),
            ]),
        ]);
    }
}
