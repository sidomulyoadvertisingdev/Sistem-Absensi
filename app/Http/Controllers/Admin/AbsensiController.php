<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\User;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    /**
     * =================================================
     * INPUT ABSENSI MANUAL — SYNC TELAT JADWAL
     * =================================================
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

        $absensi = Absensi::firstOrCreate(
            [
                'user_id' => $request->user_id,
                'tanggal' => $request->tanggal,
            ],
            [
                'status' => 'hadir',
                'menit_terlambat' => 0,
            ]
        );

        if ($request->hasFile('foto')) {
            $absensi->foto = $request->file('foto')->store('absensi', 'public');
        }

        $hari = strtolower(
            Carbon::parse($request->tanggal)
                ->locale('id')
                ->isoFormat('dddd')
        );

        $jadwal = WorkSchedule::where('user_id', $request->user_id)
            ->where('hari', $hari)
            ->where('aktif', true)
            ->first();

        /*
        =====================================
        MASUK
        =====================================
        */
        if ($request->aksi === 'masuk') {

            $absensi->jam_masuk = $request->jam;

        }

        /*
        =====================================
        ISTIRAHAT
        =====================================
        */
        if ($request->aksi === 'istirahat_mulai') {
            $absensi->istirahat_mulai = $request->jam;
        }

        if ($request->aksi === 'istirahat_selesai') {
            $absensi->istirahat_selesai = $request->jam;
        }

        /*
        =====================================
        PULANG
        =====================================
        */
        if ($request->aksi === 'pulang') {

            $absensi->jam_pulang = $request->jam;

        }

        /*
        =====================================
        FINAL STATUS
        =====================================
        */
        $menitTerlambat = $absensi->menit_terlambat ?? 0;
        $status = $absensi->status ?? 'hadir';

        if ($jadwal) {
            $menitTerlambat = 0;

            if ($absensi->jam_masuk && $jadwal->jam_masuk) {
                $jamMasuk = Carbon::parse(
                    $request->tanggal.' '.$absensi->jam_masuk
                );

                $batas = Carbon::parse(
                    $request->tanggal.' '.$jadwal->jam_masuk
                );

                if (!empty($jadwal->toleransi_masuk)) {
                    $batas->addMinutes($jadwal->toleransi_masuk);
                }

                if ($jamMasuk->gt($batas)) {
                    $menitTerlambat += $batas->diffInMinutes($jamMasuk);
                }
            }

            if ($absensi->jam_pulang && $jadwal->jam_pulang) {
                $jamPulang = Carbon::parse(
                    $request->tanggal.' '.$absensi->jam_pulang
                );

                $batas = Carbon::parse(
                    $request->tanggal.' '.$jadwal->jam_pulang
                );

                if ($jamPulang->lt($batas)) {
                    $menitTerlambat += $jamPulang->diffInMinutes($batas);
                }
            }

            $status = $menitTerlambat > 0 ? 'terlambat' : 'hadir';
        }

        $absensi->menit_terlambat = $menitTerlambat;
        $absensi->status = $status;

        $absensi->save();

        return redirect()
            ->route('admin.absensi')
            ->with('success', 'Absensi berhasil diperbarui');
    }

    /**
     * =================================================
     * IMPORT CSV — SUDAH BENAR (TIDAK DIUBAH)
     * =================================================
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = fopen($request->file('file')->getRealPath(), 'r');
        fgetcsv($file);

        DB::beginTransaction();

        $berhasil = 0;
        $gagal = 0;

        try {
            while (($row = fgetcsv($file)) !== false) {

                if (count($row) < 2) {
                    $gagal++;
                    continue;
                }

                try {
                    $tanggal = Carbon::parse(trim($row[0]))->format('Y-m-d');
                } catch (\Exception $e) {
                    $gagal++;
                    continue;
                }

                $nama = trim(preg_replace('/\s+/', ' ', $row[1]));

                $jamMasuk = $row[2] ?? null;
                $istirahatMulai = $row[3] ?? null;
                $istirahatSelesai = $row[4] ?? null;
                $jamPulang = $row[5] ?? null;

                $user = User::where('role', User::ROLE_KARYAWAN)
                    ->where('name', 'LIKE', $nama)
                    ->first();

                if (!$user) {
                    $gagal++;
                    continue;
                }

                $hari = strtolower(
                    Carbon::parse($tanggal)->locale('id')->isoFormat('dddd')
                );

                $jadwal = WorkSchedule::where('user_id', $user->id)
                    ->where('hari', $hari)
                    ->where('aktif', true)
                    ->first();

                $menitTerlambat = 0;

                if ($jadwal) {

                    if ($jamMasuk && $jadwal->jam_masuk) {
                        $masuk = Carbon::parse("$tanggal $jamMasuk");

                        $batas = Carbon::parse("$tanggal {$jadwal->jam_masuk}")
                            ->addMinutes($jadwal->toleransi_masuk ?? 0);

                        if ($masuk->gt($batas)) {
                            $menitTerlambat += $batas->diffInMinutes($masuk);
                        }
                    }

                    if ($jamPulang && $jadwal->jam_pulang) {
                        $pulang = Carbon::parse("$tanggal $jamPulang");
                        $batas = Carbon::parse("$tanggal {$jadwal->jam_pulang}");

                        if ($pulang->lt($batas)) {
                            $menitTerlambat += $pulang->diffInMinutes($batas);
                        }
                    }
                }

                Absensi::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'tanggal' => $tanggal,
                    ],
                    [
                        'jam_masuk' => $jamMasuk,
                        'istirahat_mulai' => $istirahatMulai,
                        'istirahat_selesai' => $istirahatSelesai,
                        'jam_pulang' => $jamPulang,
                        'menit_terlambat' => $menitTerlambat,
                        'status' => $menitTerlambat > 0 ? 'terlambat' : 'hadir',
                        'locked' => false,
                    ]
                );

                $berhasil++;
            }

            fclose($file);

            if ($berhasil === 0) {
                DB::rollBack();
                return back()->with('error', 'Import gagal: data tidak cocok.');
            }

            DB::commit();

            return redirect()
                ->route('admin.absensi')
                ->with('success', "Import berhasil: {$berhasil}, dilewati: {$gagal}");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Import gagal: '.$e->getMessage());
        }
    }

    /**
     * =================================================
     * TEMPLATE CSV
     * =================================================
     */
    public function exportTemplateCsv(): StreamedResponse
    {
        $filename = 'template-absensi.csv';

        return response()->stream(function () {

            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'tanggal',
                'nama',
                'jam_masuk',
                'istirahat_mulai',
                'istirahat_selesai',
                'jam_pulang',
            ]);

            fputcsv($handle, [
                '2026-01-27',
                'Albiatun',
                '08:10',
                '12:00',
                '13:00',
                '17:00',
            ]);

            fclose($handle);

        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }
}
