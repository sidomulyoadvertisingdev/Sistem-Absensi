<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\User;
use App\Models\WorkSchedule;
use Carbon\Carbon;

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
            ->orderBy('id', 'desc')
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
         * ==================================================
         * Ambil / buat absensi di tanggal yang sama
         * ==================================================
         */
        $absensi = Absensi::firstOrCreate(
            [
                'user_id' => $request->user_id,
                'tanggal' => $request->tanggal,
            ],
            [
                // default, akan ditentukan ulang saat MASUK
                'status' => 'hadir',
            ]
        );

        /**
         * ==================================================
         * SIMPAN FOTO (OPSIONAL)
         * ==================================================
         */
        if ($request->hasFile('foto')) {
            $absensi->foto = $request->file('foto')
                ->store('absensi', 'public');
        }

        /**
         * ==================================================
         * AKSI MASUK → HITUNG STATUS HADIR / TERLAMBAT
         * ==================================================
         */
        if ($request->aksi === 'masuk') {

            $jamMasuk = Carbon::createFromFormat(
                'Y-m-d H:i',
                $request->tanggal . ' ' . $request->jam
            );

            // Ambil jadwal kerja hari tersebut
            $hari = strtolower($jamMasuk->locale('id')->isoFormat('dddd'));

            $jadwal = WorkSchedule::where('user_id', $request->user_id)
                ->where('hari', $hari)
                ->where('aktif', true)
                ->first();

            // Default status
            $status = 'hadir';

            if ($jadwal && $jadwal->jam_masuk) {
                $batasTerlambat = Carbon::parse(
                    $request->tanggal . ' ' . $jadwal->jam_masuk
                )->addMinutes(15);

                $status = $jamMasuk->lte($batasTerlambat)
                    ? 'hadir'
                    : 'terlambat';
            }

            $absensi->jam_masuk = $request->jam;
            $absensi->status    = $status;
        }

        /**
         * ==================================================
         * AKSI LAIN (TIDAK MENGUBAH STATUS)
         * ==================================================
         */
        if ($request->aksi === 'istirahat_mulai') {
            $absensi->istirahat_mulai = $request->jam;
        }

        if ($request->aksi === 'istirahat_selesai') {
            $absensi->istirahat_selesai = $request->jam;
        }

        if ($request->aksi === 'pulang') {
            $absensi->jam_pulang = $request->jam;
        }

        $absensi->save();

        return redirect()
            ->route('admin.absensi')
            ->with('success', 'Absensi berhasil diperbarui');
    }
}
