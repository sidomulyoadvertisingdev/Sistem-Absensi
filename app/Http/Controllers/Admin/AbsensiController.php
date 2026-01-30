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
     * SIMPAN / UPDATE ABSENSI
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

        // ===============================
        // AMBIL / BUAT ABSENSI HARIAN
        // ===============================
        $absensi = Absensi::firstOrCreate(
            [
                'user_id' => $request->user_id,
                'tanggal' => $request->tanggal,
            ],
            [
                'status'           => 'hadir',
                'menit_terlambat'  => 0,
            ]
        );

        if ($request->hasFile('foto')) {
            $absensi->foto = $request->file('foto')
                ->store('absensi', 'public');
        }

        // ===============================
        // AMBIL JADWAL KERJA SESUAI HARI
        // ===============================
        $hari = strtolower(
            Carbon::parse($request->tanggal)
                ->locale('id')
                ->isoFormat('dddd')
        );

        $jadwal = WorkSchedule::where('user_id', $request->user_id)
            ->where('hari', $hari)
            ->where('aktif', true)
            ->first();

        /**
         * ==================================================
         * AKSI MASUK (SATU-SATUNYA TEMPAT HITUNG TELAT)
         * ==================================================
         */
        if ($request->aksi === 'masuk') {

            $jamMasuk = Carbon::parse($request->tanggal . ' ' . $request->jam);

            $status          = 'hadir';
            $menitTerlambat  = 0;

            // 🔥 hanya jika ada jadwal & jam masuk
            if ($jadwal && $jadwal->jam_masuk) {

                $batasMasuk = Carbon::parse(
                    $request->tanggal . ' ' . $jadwal->jam_masuk
                )->addMinutes($jadwal->toleransi_masuk ?? 0);

                if ($jamMasuk->gt($batasMasuk)) {
                    $status = 'terlambat_masuk';
                    $menitTerlambat = $batasMasuk->diffInMinutes($jamMasuk);
                }
            }

            $absensi->jam_masuk        = $request->jam;
            $absensi->status           = $status;
            $absensi->menit_terlambat  = $menitTerlambat;
        }

        /**
         * ==================================================
         * AKSI ISTIRAHAT
         * ==================================================
         */
        if ($request->aksi === 'istirahat_mulai') {
            $absensi->istirahat_mulai = $request->jam;
        }

        if ($request->aksi === 'istirahat_selesai') {
            $absensi->istirahat_selesai = $request->jam;
        }

        /**
         * ==================================================
         * AKSI PULANG (PULANG CEPAT ≠ TERLAMBAT MASUK)
         * ==================================================
         */
        if ($request->aksi === 'pulang') {

            $jamPulang = Carbon::parse($request->tanggal . ' ' . $request->jam);

            if ($jadwal && $jadwal->jam_pulang) {

                $batasPulang = Carbon::parse(
                    $request->tanggal . ' ' . $jadwal->jam_pulang
                );

                if ($jamPulang->lt($batasPulang)) {
                    $absensi->status = 'pulang_cepat';
                }
            }

            $absensi->jam_pulang = $request->jam;
        }

        $absensi->save();

        return redirect()
            ->route('admin.absensi')
            ->with('success', 'Absensi berhasil diperbarui');
    }
}
