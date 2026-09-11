<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lembur;
use App\Models\User;
use Carbon\Carbon;

class LemburController extends Controller
{
    /**
     * LIST LEMBUR
     */
    public function index()
    {
        $data = Lembur::with(['user', 'approver'])
            ->orderBy('tanggal', 'desc')
            ->paginate(20);

        return view('admin.lembur.index', compact('data'));
    }

    /**
     * FORM INPUT LEMBUR
     */
    public function create()
    {
        $users = User::orderBy('name')->get();
        return view('admin.lembur.create', compact('users'));
    }

    /**
     * SIMPAN LEMBUR
     */
    public function store(Request $request)
    {
        // ✅ VALIDASI DASAR (tanpa after)
        $request->validate([
            'user_id'    => 'required|exists:users,id',
            'tanggal'    => 'required|date',
            'jam_mulai'  => 'required|date_format:H:i',
            'jam_selesai'=> 'required|date_format:H:i',
            'keterangan' => 'nullable|string',
        ]);

        // ✅ VALIDASI WAKTU PAKAI CARBON
        $mulai   = Carbon::createFromFormat('H:i', $request->jam_mulai);
        $selesai = Carbon::createFromFormat('H:i', $request->jam_selesai);

        // Jika jam selesai lebih kecil, berarti lewat tengah malam
        if ($selesai->lessThanOrEqualTo($mulai)) {
            $selesai->addDay();
        }

        // Minimal lembur 30 menit (opsional tapi recommended)
        if ($mulai->diffInMinutes($selesai) < 30) {
            return back()
                ->withErrors(['jam_selesai' => 'Durasi lembur minimal 30 menit'])
                ->withInput();
        }

        // SIMPAN
        // Input manual admin = admin sendiri yang input & bertindak sebagai
        // approver sekaligus -> langsung final, tidak perlu approve ulang.
        Lembur::create([
            'user_id'     => $request->user_id,
            'tanggal'     => $request->tanggal,
            'jam_mulai'   => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'keterangan'  => $request->keterangan,
            'status'      => 'approved', // admin langsung ACC
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.lembur')
            ->with('success', 'Lembur berhasil ditambahkan');
    }

    /**
     * SETUJUI UNTUK DIHITUNG KE GAJI
     * ===============================
     * Murni menyetujui nominal lembur masuk perhitungan gaji.
     * TIDAK mengubah `status` (progres kerja) -- bisa dipanggil kapan saja,
     * baik saat lembur masih berjalan (requested) maupun sudah selesai
     * (finished). Karyawan tetap bisa mengajukan & menyelesaikan lembur
     * tanpa menunggu langkah ini.
     */
    public function approve($id)
    {
        Lembur::findOrFail($id)->update([
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Lembur disetujui dan akan dihitung ke gaji');
    }

    /**
     * TOLAK PENGAJUAN LEMBUR
     * ===============================
     * Menandai lembur sebagai ditolak -- tidak akan pernah dihitung ke gaji
     * dan tidak bisa diselesaikan/disetujui lagi.
     */
    public function reject($id)
    {
        Lembur::findOrFail($id)->update([
            'status' => 'rejected',
        ]);

        return back()->with('success', 'Lembur ditolak');
    }

    /**
     * HAPUS LEMBUR
     */
    public function destroy($id)
    {
        Lembur::findOrFail($id)->delete();

        return redirect()
            ->route('admin.lembur')
            ->with('success', 'Lembur berhasil dihapus');
    }
}
