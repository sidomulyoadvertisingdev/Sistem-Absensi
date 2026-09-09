<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PeerReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PeerReportController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        if (!in_array($status, ['pending', 'approved', 'rejected', 'all'], true)) {
            $status = 'pending';
        }

        $reports = PeerReport::query()
            ->with(['reporter:id,name,nik', 'reportedUser:id,name,nik,jabatan', 'reviewer:id,name'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $counts = [
            'pending' => PeerReport::where('status', 'pending')->count(),
            'approved' => PeerReport::where('status', 'approved')->count(),
            'rejected' => PeerReport::where('status', 'rejected')->count(),
        ];

        return view('admin.peer-reports.index', compact('reports', 'status', 'counts'));
    }

    public function approve(Request $request, PeerReport $peerReport)
    {
        $data = $request->validate([
            'catatan_admin' => ['nullable', 'string', 'max:2000'],
        ]);

        $peerReport->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'catatan_admin' => $data['catatan_admin'] ?? $peerReport->catatan_admin,
        ]);

        return back()->with('success', 'Laporan disetujui. Akan tampil di profil karyawan terlapor (tanpa identitas pelapor).');
    }

    public function reject(Request $request, PeerReport $peerReport)
    {
        $data = $request->validate([
            'catatan_admin' => ['nullable', 'string', 'max:2000'],
        ]);

        $peerReport->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'catatan_admin' => $data['catatan_admin'] ?? $peerReport->catatan_admin,
        ]);

        return back()->with('success', 'Laporan ditolak.');
    }

    public function destroy(PeerReport $peerReport)
    {
        if ($peerReport->bukti_path) {
            Storage::disk('public')->delete($peerReport->bukti_path);
        }
        $peerReport->delete();

        return back()->with('success', 'Laporan dihapus.');
    }
}
