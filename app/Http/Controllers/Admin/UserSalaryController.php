<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSalary;
use App\Models\Lembur;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserSalaryController extends Controller
{
    /**
     * ===============================
     * LIST GAJI PER USER
     * ===============================
     */
    public function index()
    {
        $users = User::with('salary')->orderBy('name')->get();

        return view('admin.gaji.index', compact('users'));
    }

    /**
     * ===============================
     * FORM EDIT GAJI USER
     * ===============================
     */
    public function edit(User $user)
    {
        return view('admin.gaji.edit', compact('user'));
    }

    /**
     * ===============================
     * SIMPAN / UPDATE GAJI USER
     * ===============================
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'gaji_pokok'     => 'required|numeric|min:0',
            'uang_makan'     => 'nullable|numeric|min:0',
            'transport'      => 'nullable|numeric|min:0',
            'lembur_per_jam' => 'nullable|numeric|min:0',
        ]);

        UserSalary::updateOrCreate(
            ['user_id' => $user->id],
            [
                'gaji_pokok'     => $request->gaji_pokok,
                'uang_makan'     => $request->uang_makan ?? 0,
                'transport'      => $request->transport ?? 0,
                'lembur_per_jam' => $request->lembur_per_jam ?? 0,
                'aktif'          => true,
            ]
        );

        return redirect()
            ->route('admin.gaji')
            ->with('success', 'Data gaji berhasil disimpan');
    }

    /**
     * ===============================
     * 🖨 CETAK SLIP GAJI (PDF)
     * ===============================
     */
    public function slipPdf(User $user)
    {
        $salary = $user->salary;

        if (!$salary || !$salary->aktif) {
            abort(404, 'Gaji belum diatur atau tidak aktif');
        }

        // ===============================
        // BULAN & TAHUN AMAN (TANPA PARSE STRING)
        // ===============================
        $now   = Carbon::now();
        $bulan = $now->month; // angka
        $tahun = $now->year;  // angka

        // ===============================
        // HITUNG TOTAL JAM LEMBUR
        // ===============================
        $totalJamLembur = Lembur::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->where('status', 'approved')
            ->get()
            ->sum(function ($item) {
                return Carbon::parse($item->jam_mulai)
                    ->diffInHours(Carbon::parse($item->jam_selesai));
            });

        // ===============================
        // HITUNG TOTAL LEMBUR & GAJI
        // ===============================
        $totalLembur = $totalJamLembur * ($salary->lembur_per_jam ?? 0);

        $totalGaji =
            $salary->gaji_pokok +
            $salary->uang_makan +
            $salary->transport +
            $totalLembur;

        // ===============================
        // GENERATE PDF
        // ===============================
        $pdf = Pdf::loadView('admin.gaji.slip-pdf', [
            'user'           => $user,
            'salary'         => $salary,
            'totalJamLembur' => $totalJamLembur,
            'totalLembur'    => $totalLembur,
            'totalGaji'      => $totalGaji,
            'bulan'          => $now->translatedFormat('F Y'), // hanya untuk tampilan
        ]);

        return $pdf->stream(
            'Slip-Gaji-' . $user->name . '-' . $now->format('m-Y') . '.pdf'
        );
    }
}
