<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSalary;
use App\Models\Lembur;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserSalaryController extends Controller
{
    /**
     * ===============================
     * LIST GAJI (HANYA KARYAWAN)
     * ===============================
     */
    public function index(Request $request)
    {
        // BULAN DEFAULT (YYYY-MM)
        $bulan = $request->bulan ?? now()->format('Y-m');

        $users = User::with('salary')
            ->where('role', User::ROLE_KARYAWAN)
            ->orderBy('name')
            ->get();

        return view('admin.gaji.index', compact('users', 'bulan'));
    }

    /**
     * ===============================
     * FORM EDIT GAJI USER
     * ===============================
     */
    public function edit(User $user)
    {
        if (!$user->isKaryawan()) {
            abort(403, 'Hanya karyawan yang boleh memiliki gaji');
        }

        return view('admin.gaji.edit', compact('user'));
    }

    /**
     * ===============================
     * SIMPAN / UPDATE GAJI USER
     * ===============================
     */
    public function update(Request $request, User $user)
    {
        if (!$user->isKaryawan()) {
            abort(403, 'Hanya karyawan yang boleh memiliki gaji');
        }

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
    public function slipPdf(Request $request, User $user)
    {
        if (!$user->isKaryawan()) {
            abort(403, 'Slip gaji hanya untuk karyawan');
        }

        $salary = $user->salary;

        if (!$salary || !$salary->aktif) {
            abort(404, 'Gaji belum diatur atau tidak aktif');
        }

        /**
         * ===============================
         * BULAN & TAHUN
         * ===============================
         */
        $bulanInput = $request->bulan ?? now()->format('Y-m');
        $date       = Carbon::createFromFormat('Y-m', $bulanInput);
        $bulan      = $date->month;
        $tahun      = $date->year;

        /**
         * ===============================
         * HITUNG LEMBUR
         * ===============================
         */
        $totalJamLembur = Lembur::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->where('status', 'approved')
            ->get()
            ->sum(function ($item) {
                return Carbon::parse($item->jam_mulai)
                    ->diffInHours(Carbon::parse($item->jam_selesai));
            });

        $totalLembur = $totalJamLembur * ($salary->lembur_per_jam ?? 0);

        /**
         * ===============================
         * 🔥 BONUS JOB TODO (PALING AMAN)
         * ===============================
         * - Ambil dari pivot job_todo_user
         * - completed_at = sumber kebenaran
         */
        $jobBonus = DB::table('job_todo_user')
            ->join('job_todos', 'job_todos.id', '=', 'job_todo_user.job_todo_id')
            ->where('job_todo_user.user_id', $user->id)
            ->where('job_todo_user.status', 'completed')
            ->whereMonth('job_todo_user.completed_at', $bulan)
            ->whereYear('job_todo_user.completed_at', $tahun)
            ->select(
                'job_todos.title',
                'job_todos.bonus',
                'job_todo_user.completed_at'
            )
            ->get();

        $totalBonusJob = $jobBonus->sum('bonus');

        /**
         * ===============================
         * TOTAL GAJI
         * ===============================
         */
        $totalGaji =
            $salary->gaji_pokok +
            $salary->uang_makan +
            $salary->transport +
            $totalLembur +
            $totalBonusJob;

        /**
         * ===============================
         * GENERATE PDF
         * ===============================
         */
        $pdf = Pdf::loadView('admin.gaji.slip-pdf', [
            'user'           => $user,
            'salary'         => $salary,
            'totalJamLembur' => $totalJamLembur,
            'totalLembur'    => $totalLembur,
            'jobBonus'       => $jobBonus,      // 🔎 detail bonus per job
            'totalBonusJob'  => $totalBonusJob, // 💰 total bonus
            'totalGaji'      => $totalGaji,
            'bulan'          => $date->translatedFormat('F Y'),
        ]);

        return $pdf->stream(
            'Slip-Gaji-' . $user->name . '-' . $date->format('m-Y') . '.pdf'
        );
    }
}
