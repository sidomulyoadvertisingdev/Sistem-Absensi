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
        $data = Lembur::with('user')
            ->orderBy('tanggal', 'desc')
            ->get();

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
        Lembur::create([
            'user_id'     => $request->user_id,
            'tanggal'     => $request->tanggal,
            'jam_mulai'   => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'keterangan'  => $request->keterangan,
            'status'      => 'approved', // admin langsung ACC
        ]);

        return redirect()
            ->route('admin.lembur')
            ->with('success', 'Lembur berhasil ditambahkan');
    }

    /**
     * APPROVE (OPSIONAL)
     */
    public function approve($id)
    {
        Lembur::findOrFail($id)->update([
            'status' => 'approved'
        ]);

        return back()->with('success', 'Lembur disetujui');
    }
}
