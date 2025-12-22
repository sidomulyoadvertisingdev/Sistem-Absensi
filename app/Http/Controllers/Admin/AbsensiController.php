<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\User;

class AbsensiController extends Controller
{
    /**
     * ===============================
     * LIST ABSENSI (ADMIN)
     * ===============================
     */
    public function index()
    {
        $data = Absensi::with('user')
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('admin.absensi.index', compact('data'));
    }

    /**
     * ===============================
     * FORM INPUT ABSENSI MANUAL
     * ===============================
     */
    public function create()
    {
        $users = User::orderBy('name')->get();

        return view('admin.absensi.create', compact('users'));
    }

    /**
     * ===============================
     * SIMPAN / UPDATE ABSENSI (ADMIN)
     * ===============================
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal' => 'required|date',
            'aksi'    => 'required|in:masuk,istirahat_mulai,istirahat_selesai,pulang',
            'jam'     => 'required|date_format:H:i',
            'foto'    => 'nullable|image|max:2048',
        ]);

        /**
         * 🔑 Ambil / buat absensi DI TANGGAL YANG SAMA
         * → supaya tidak dobel baris
         */
        $absensi = Absensi::firstOrCreate(
            [
                'user_id' => $request->user_id,
                'tanggal' => $request->tanggal,
            ],
            [
                'status' => 'hadir',
            ]
        );

        /**
         * 📸 Simpan foto (opsional)
         */
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('absensi', 'public');
            $absensi->foto = $path;
        }

        /**
         * 🎯 Mapping AKSI → KOLOM ABSENSI
         */
        switch ($request->aksi) {
            case 'masuk':
                $absensi->jam_masuk = $request->jam;
                break;

            case 'istirahat_mulai':
                $absensi->istirahat_mulai = $request->jam;
                break;

            case 'istirahat_selesai':
                $absensi->istirahat_selesai = $request->jam;
                break;

            case 'pulang':
                $absensi->jam_pulang = $request->jam;
                break;
        }

        $absensi->save();

        /**
         * ✅ PENTING: redirect ke route yang BENAR
         */
        return redirect()
            ->route('admin.absensi')
            ->with('success', 'Absensi berhasil diperbarui');
    }
}
