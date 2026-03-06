<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Lembur;
use App\Models\SalaryDeductionRule;
use App\Models\User;
use App\Services\EarlyLeaveSalaryService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceIntegrationReportController extends Controller
{
    public function report(Request $request): JsonResponse
    {
        if ($response = $this->ensureAuthorized($request, 'integration.attendance.report.read')) {
            return $response;
        }

        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'search' => ['nullable', 'string', 'max:100'],
            'outlet_id' => ['nullable'],
            'outlet_name' => ['nullable', 'string', 'max:100'],
            'outlet' => ['nullable', 'string', 'max:100'],
            'penempatan' => ['nullable', 'string', 'max:100'],
        ]);

        $dateFrom = !empty($validated['date_from'])
            ? Carbon::parse((string) $validated['date_from'])->startOfDay()
            : now()->startOfMonth()->startOfDay();
        $dateTo = !empty($validated['date_to'])
            ? Carbon::parse((string) $validated['date_to'])->endOfDay()
            : now()->endOfDay();

        if ($dateFrom->gt($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo->copy()->startOfDay(), $dateFrom->copy()->endOfDay()];
        }

        $search = trim((string) ($validated['search'] ?? ''));
        $penempatanFilter = $this->resolvePenempatanFilter($request);
        $payrollMonths = $this->resolvePayrollMonths($dateFrom, $dateTo);

        $usersQuery = User::query()
            ->with('salary')
            ->where('role', User::ROLE_KARYAWAN);

        if ($search !== '') {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('nik', 'like', '%' . $search . '%')
                    ->orWhere('jabatan', 'like', '%' . $search . '%')
                    ->orWhere('penempatan', 'like', '%' . $search . '%');
            });
        }

        if ($penempatanFilter !== '') {
            $usersQuery->where('penempatan', 'like', '%' . $penempatanFilter . '%');
        }

        $users = $usersQuery->orderBy('name')->get();

        $estimatedWorkingDays = $this->estimateWorkingDays($dateFrom, $dateTo);

        $rows = $users->map(function (User $user) use ($dateFrom, $dateTo, $payrollMonths, $estimatedWorkingDays) {
            $absensis = Absensi::query()
                ->where('user_id', $user->id)
                ->whereBetween('tanggal', [$dateFrom->toDateString(), $dateTo->toDateString()])
                ->get();

            $hadir = (int) $absensis->where('status', 'hadir')->count();
            $terlambat = (int) $absensis->where('status', 'terlambat')->count();
            $izin = (int) $absensis->where('status', 'izin')->count();
            $sakit = (int) $absensis->where('status', 'sakit')->count();
            $alphaManual = (int) $absensis->where('status', 'alpha')->count();

            $present = $hadir + $terlambat;
            $inferredAlpha = max(0, $estimatedWorkingDays - ($present + $izin + $sakit + $alphaManual));
            $alpha = $alphaManual + $inferredAlpha;
            $workingDays = max($estimatedWorkingDays, $present + $izin + $sakit + $alpha);

            $overtimeMinutes = (int) round(
                Lembur::query()
                    ->where('user_id', $user->id)
                    ->where('status', 'approved')
                    ->whereBetween('tanggal', [$dateFrom->toDateString(), $dateTo->toDateString()])
                    ->get()
                    ->sum(function ($lembur) {
                        if (empty($lembur->jam_mulai) || empty($lembur->jam_selesai)) {
                            return 0;
                        }

                        return Carbon::parse($lembur->jam_mulai)
                            ->diffInMinutes(Carbon::parse($lembur->jam_selesai));
                    })
            );

            $payrollAmount = 0.0;
            foreach ($payrollMonths as $month) {
                $payrollAmount += $this->calculateMonthlyPayrollAmount($user, $month);
            }

            return [
                'employee_id' => (int) $user->id,
                'employee_name' => (string) ($user->name ?? '-'),
                'outlet_name' => (string) ($user->penempatan ?? '-'),
                'position_name' => (string) ($user->jabatan ?? '-'),
                'working_days' => $workingDays,
                'present' => $present,
                'leave' => $izin,
                'sick' => $sakit,
                'absent' => $alpha,
                'late_count' => $terlambat,
                'overtime_minutes' => max(0, $overtimeMinutes),
                'payroll_amount' => round(max(0, $payrollAmount), 2),
            ];
        })->values();

        $summary = [
            'employee_count' => $rows->count(),
            'present_total' => (int) $rows->sum('present'),
            'absent_total' => (int) $rows->sum('absent'),
            'late_total' => (int) $rows->sum('late_count'),
            'overtime_minutes_total' => (int) $rows->sum('overtime_minutes'),
            'payroll_total' => round((float) $rows->sum('payroll_amount'), 2),
        ];

        return response()->json([
            'status' => 'ok',
            'message' => 'Laporan absensi berhasil diambil.',
            'data' => [
                'period' => [
                    'date_from' => $dateFrom->toDateString(),
                    'date_to' => $dateTo->toDateString(),
                ],
                'summary' => $summary,
                'rows' => $rows,
            ],
        ]);
    }

    private function ensureAuthorized(Request $request, string $ability): ?JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->isPanelAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak.',
            ], 403);
        }

        $token = $user->currentAccessToken();
        if ($request->bearerToken() && $token && !$token->can($ability)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak memiliki izin untuk endpoint ini.',
            ], 403);
        }

        return null;
    }

    private function resolvePenempatanFilter(Request $request): string
    {
        $candidates = [
            (string) $request->input('penempatan', ''),
            (string) $request->input('outlet_name', ''),
            (string) $request->input('outlet', ''),
        ];

        foreach ($candidates as $candidate) {
            $value = trim($candidate);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function resolvePayrollMonths(Carbon $dateFrom, Carbon $dateTo): Collection
    {
        $months = collect();
        $cursor = $dateFrom->copy()->startOfMonth();
        $lastMonth = $dateTo->copy()->startOfMonth();
        $limit = 0;

        while ($cursor->lte($lastMonth) && $limit < 24) {
            $months->push($cursor->copy());
            $cursor->addMonth();
            $limit++;
        }

        if ($months->isEmpty()) {
            $months->push($dateFrom->copy()->startOfMonth());
        }

        return $months;
    }

    private function estimateWorkingDays(Carbon $dateFrom, Carbon $dateTo): int
    {
        $cursor = $dateFrom->copy()->startOfDay();
        $last = $dateTo->copy()->startOfDay();
        $days = 0;

        while ($cursor->lte($last)) {
            if ($cursor->dayOfWeek !== Carbon::SUNDAY) {
                $days++;
            }
            $cursor->addDay();
        }

        return max(0, $days);
    }

    private function calculateMonthlyPayrollAmount(User $user, Carbon $period): float
    {
        $salary = $user->salary;
        if (!$salary || !$salary->aktif) {
            return 0.0;
        }

        $bulan = $period->month;
        $tahun = $period->year;
        $hariKerjaStandar = 26;

        $absensis = Absensi::query()
            ->where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get();

        $hariHadir = (int) $absensis->where('status', 'hadir')->count();
        $hariTelat = (int) $absensis->where('status', 'terlambat')->count();
        $presensi = $hariHadir + $hariTelat;
        $hariNormal = min($presensi, $hariKerjaStandar);
        $offDay = max($hariKerjaStandar - $hariNormal, 0);
        $menitTerlambat = (int) $absensis->where('status', 'terlambat')->sum('menit_terlambat');

        $tunjanganArray = [
            'tunjangan_umum' => (float) ($salary->tunjangan_umum ?? 0),
            'tunjangan_transport' => (float) ($salary->tunjangan_transport ?? 0),
            'tunjangan_thr' => (float) ($salary->tunjangan_thr ?? 0),
            'tunjangan_kesehatan' => (float) ($salary->tunjangan_kesehatan ?? 0),
        ];

        $totalTunjanganMaster = array_sum($tunjanganArray);
        $tunjanganPayroll = (bool) $salary->include_tunjangan ? $totalTunjanganMaster : 0.0;

        $gajiPerHari = (float) $salary->getGajiHarian($hariKerjaStandar);
        $earlyLeaveSalary = app(EarlyLeaveSalaryService::class)->calculate(
            user: $user,
            absensis: $absensis,
            gajiPerHari: $gajiPerHari,
            bulan: $bulan,
            tahun: $tahun,
            hariKerjaStandar: $hariKerjaStandar
        );

        $gajiNormal = (float) ($earlyLeaveSalary['gaji_normal'] ?? 0);
        $gajiTambahan = (float) ($earlyLeaveSalary['gaji_tambahan'] ?? 0);

        $totalJamLembur = Lembur::query()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get()
            ->sum(function ($lembur) {
                if (empty($lembur->jam_mulai) || empty($lembur->jam_selesai)) {
                    return 0;
                }

                return Carbon::parse($lembur->jam_mulai)
                    ->diffInMinutes(Carbon::parse($lembur->jam_selesai)) / 60;
            });
        $uangLembur = (float) $totalJamLembur * (float) ($salary->lembur_per_jam ?? 0);

        $totalBonusJob = (float) DB::table('job_todo_user')
            ->join('job_todos', 'job_todos.id', '=', 'job_todo_user.job_todo_id')
            ->where('job_todo_user.user_id', $user->id)
            ->where('job_todo_user.status', 'completed')
            ->whereMonth('job_todo_user.completed_at', $bulan)
            ->whereYear('job_todo_user.completed_at', $tahun)
            ->sum('job_todos.bonus');

        $salaryKotor = $gajiNormal + $gajiTambahan + $uangLembur + $totalBonusJob + $tunjanganPayroll;

        $rules = SalaryDeductionRule::query()->where('aktif', true)->get();
        $totalPotongan = 0.0;

        foreach ($rules as $rule) {
            if (!$rule->isApplicableForPenempatan($user->penempatan)) {
                continue;
            }

            $base = match ($rule->base_source) {
                'gaji_pokok' => (float) ($salary->gaji_pokok ?? 0),
                'tunjangan' => collect($rule->tunjangan_items ?? [])
                    ->sum(fn ($item) => (float) ($tunjanganArray[$item] ?? 0)),
                default => $salaryKotor,
            };

            if ($base <= 0) {
                continue;
            }

            if ($rule->condition_type === 'terlambat') {
                $trigger = (int) ($rule->condition_value ?? 1);

                if ($hariTelat < $trigger) {
                    continue;
                }

                if (!empty($rule->max_minutes) && $menitTerlambat < (int) $rule->max_minutes) {
                    continue;
                }

                $totalPotongan += (float) $rule->calculate($base);
                continue;
            }

            if ($rule->condition_type === 'off_day' && $offDay > 0) {
                $totalPotongan += (float) $rule->calculate($base);
            }
        }

        $trainingInfo = $salary->calculateTrainingDeduction($salaryKotor, $period);
        $totalPotongan += (float) ($trainingInfo['deduction_nominal'] ?? 0);

        return (float) max($salaryKotor - $totalPotongan, 0);
    }
}
