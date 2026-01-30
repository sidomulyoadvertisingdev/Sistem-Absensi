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
    public function index()
    {
        $data = Absensi::with('user')
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.absensi.index', compact('data'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get();
        return view('admin.absensi.create', compact('users'));
    }

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
         * ===============================
         * ABSENSI HARIAN
         * ===============================
         */
        $absensi = Absensi::firstOrCreate(
            [
                'user_id' => $request->user_id,
                'tanggal' => $request->tanggal,
            ],
            [
                'status'          => 'hadir',
                'menit_terlambat' => 0,
            ]
        );

        if ($request->hasFile('foto')) {
            $absensi->foto = $request->file('foto')
                ->store('absensi', 'public');
        }

        /**
         * ===============================
         * JADWAL KERJA
         * ===============================
         */
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
         * ===============================
         * MASUK (TELAT MASUK)
         * ===============================
         */
        if ($request->aksi === 'masuk') {

            $jamMasuk = Carbon::parse($request->tanggal . ' ' . $request->jam);
            $telatMasuk = 0;

            if ($jadwal && $jadwal->jam_masuk) {

                $batasMasuk = Carbon::parse(
                    $request->tanggal . ' ' . $jadwal->jam_masuk
                )->addMinutes($jadwal->toleransi_masuk ?? 0);

                // 🔥 hanya jika lebih lambat
                if ($jamMasuk->gt($batasMasuk)) {
                    $telatMasuk = $batasMasuk->diffInMinutes($jamMasuk);
                }
            }

            $absensi->jam_masuk = $request->jam;
            $absensi->menit_terlambat += $telatMasuk;
        }

        /**
         * ===============================
         * ISTIRAHAT
         * ===============================
         */
        if ($request->aksi === 'istirahat_mulai') {
            $absensi->istirahat_mulai = $request->jam;
        }

        if ($request->aksi === 'istirahat_selesai') {
            $absensi->istirahat_selesai = $request->jam;
        }

        /**
         * ===============================
         * PULANG (PULANG CEPAT)
         * ===============================
         */
        if ($request->aksi === 'pulang') {

            $jamPulang = Carbon::parse($request->tanggal . ' ' . $request->jam);
            $pulangCepat = 0;

            if ($jadwal && $jadwal->jam_pulang) {

                $batasPulang = Carbon::parse(
                    $request->tanggal . ' ' . $jadwal->jam_pulang
                );

                // 🔥 hanya jika pulang lebih cepat
                if ($jamPulang->lt($batasPulang)) {
                    $pulangCepat = $jamPulang->diffInMinutes($batasPulang);
                }
            }

            $absensi->jam_pulang = $request->jam;
            $absensi->menit_terlambat += $pulangCepat;
        }

        /**
         * ===============================
         * STATUS FINAL (SATU SUMBER KEBENARAN)
         * ===============================
         */
        $absensi->status = $absensi->menit_terlambat > 0
            ? 'terlambat'
            : 'hadir';

        $absensi->save();

        return redirect()
            ->route('admin.absensi')
            ->with('success', 'Absensi berhasil diperbarui');
    }
}
