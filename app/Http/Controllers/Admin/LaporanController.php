<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Absensi;
use App\Models\Lembur;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // default bulan & tahun sekarang
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $users = User::orderBy('name')->get();

        $laporan = [];

        foreach ($users as $user) {

            // ABSENSI BULANAN
            $absensi = Absensi::where('user_id', $user->id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->get();

            // LEMBUR BULANAN
            $lembur = Lembur::where('user_id', $user->id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->get();

            // HITUNG JAM LEMBUR
$totalMenitLembur = 0;

foreach ($lembur as $l) {

    // ⛔ GABUNGKAN TANGGAL + JAM (INI KUNCI UTAMA)
    $mulai = Carbon::parse($l->tanggal.' '.$l->jam_mulai);
    $selesai = Carbon::parse($l->tanggal.' '.$l->jam_selesai);

    // ✅ JIKA LEWAT TENGAH MALAM
    if ($selesai->lessThan($mulai)) {
        $selesai->addDay();
    }

    $totalMenitLembur += $mulai->diffInMinutes($selesai);
}


            $laporan[] = [
                'nama'        => $user->name,
                'hadir'       => $absensi->where('status', 'hadir')->count(),
                'izin'        => $absensi->where('status', 'izin')->count(),
                'sakit'       => $absensi->where('status', 'sakit')->count(),
                'hari_kerja'  => $absensi->count(),
                'lembur_jam'  => floor($totalMenitLembur / 60),
                'lembur_menit'=> $totalMenitLembur % 60,
            ];
        }

        return view('admin.laporan.index', compact(
            'laporan',
            'bulan',
            'tahun'
        ));
    }
}
 