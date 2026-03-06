<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Lembur;
use App\Models\SalaryDeductionRule;
use App\Models\User;
use App\Models\UserSalary;
use App\Services\EarlyLeaveSalaryService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendancePayrollIntegrationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (!$request->user() || !$request->user()->isPanelAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak.',
            ], 403);
        }

        $validated = $request->validate([
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'year' => ['nullable', 'integer', 'min:2020', 'max:2100'],
            'search' => ['nullable', 'string', 'max:100'],
            'penempatan' => ['nullable', 'string', 'max:100'],
            'outlet_name' => ['nullable', 'string', 'max:100'],
            'outlet' => ['nullable', 'string', 'max:100'],
        ]);

        $month = (int) ($validated['month'] ?? now()->month);
        $year = (int) ($validated['year'] ?? now()->year);
        $search = trim((string) ($validated['search'] ?? ''));
        $penempatanFilter = $this->resolvePenempatanFilter($request);
        $periodDate = Carbon::create($year, $month, 1)->startOfMonth();
        $periodYm = $periodDate->format('Y-m');

        $query = User::query()
            ->with('salary')
            ->where('role', User::ROLE_KARYAWAN);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('nik', 'like', '%' . $search . '%')
                    ->orWhere('jabatan', 'like', '%' . $search . '%')
                    ->orWhere('penempatan', 'like', '%' . $search . '%');
            });
        }

        if ($penempatanFilter !== '') {
            $query->where('penempatan', 'like', '%' . $penempatanFilter . '%');
        }

        $users = $query->orderBy('name')->get();

        $rows = $users->values()->map(function (User $user, int $index) use ($periodDate, $periodYm) {
            $salary = $user->salary;
            $payroll = $this->calculatePayrollSummary($user, $periodDate);

            $isPaidForPeriod = $salary ? (bool) $salary->isPaidFor($periodYm) : false;
            $paidAt = ($salary && $isPaidForPeriod && $salary->paid_at)
                ? Carbon::parse($salary->paid_at)->toDateTimeString()
                : null;

            return array_merge([
                'no' => $index + 1,
                'employee_id' => (int) $user->id,
                'employee_name' => (string) ($user->name ?? '-'),
                'position_name' => (string) ($user->jabatan ?? '-'),
                'outlet_name' => (string) ($user->penempatan ?? '-'),
                'salary_exists' => (bool) $salary,
                'salary_active' => (bool) ($salary->aktif ?? false),
                'is_paid' => $isPaidForPeriod,
                'payroll_period' => $periodYm,
                'paid_at' => $paidAt,
                'can_pay' => (bool) (($salary->aktif ?? false) && !$isPaidForPeriod),
                'salary' => [
                    'gaji_pokok' => (float) ($salary->gaji_pokok ?? 0),
                    'gaji_harian' => (float) ($salary->gaji_harian ?? 0),
                    'gaji_harian_mode' => (string) ($salary->gaji_harian_mode ?? 'manual'),
                    'include_tunjangan' => (bool) ($salary->include_tunjangan ?? false),
                    'tunjangan_umum' => (float) ($salary->tunjangan_umum ?? 0),
                    'tunjangan_transport' => (float) ($salary->tunjangan_transport ?? 0),
                    'tunjangan_thr' => (float) ($salary->tunjangan_thr ?? 0),
                    'tunjangan_kesehatan' => (float) ($salary->tunjangan_kesehatan ?? 0),
                    'lembur_per_jam' => (float) ($salary->lembur_per_jam ?? 0),
                    'aktif' => (bool) ($salary->aktif ?? false),
                    'training_enabled' => (bool) ($salary->training_enabled ?? false),
                    'training_start_date' => !empty($salary?->training_start_date)
                        ? Carbon::parse($salary->training_start_date)->toDateString()
                        : null,
                    'training_duration_days' => (int) ($salary->training_duration_days ?? 0),
                    'training_deduction_type' => (string) ($salary->training_deduction_type ?? 'percentage'),
                    'training_deduction_value' => (float) ($salary->training_deduction_value ?? 0),
                ],
            ], $payroll);
        });

        return response()->json([
            'status' => 'ok',
            'message' => 'Data gaji berhasil diambil.',
            'data' => [
                'period' => [
                    'month' => $month,
                    'year' => $year,
                    'ym' => $periodYm,
                ],
                'summary' => [
                    'employee_count' => $rows->count(),
                    'salary_active_count' => (int) $rows->where('salary_active', true)->count(),
                    'paid_count' => (int) $rows->where('is_paid', true)->count(),
                    'payroll_total' => (float) $rows->sum('gaji_diterima'),
                ],
                'rows' => $rows->values(),
            ],
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        if (!$request->user() || !$request->user()->isPanelAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak.',
            ], 403);
        }

        if (!$user->isKaryawan()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Karyawan tidak valid.',
            ], 422);
        }

        $request->validate([
            'gaji_pokok' => ['required', 'numeric', 'min:0'],
            'gaji_harian' => ['nullable', 'numeric', 'min:0'],
            'gaji_harian_mode' => ['nullable', 'in:manual,pokok,pokok_plus_tunjangan'],
            'tunjangan_umum' => ['nullable', 'numeric', 'min:0'],
            'tunjangan_transport' => ['nullable', 'numeric', 'min:0'],
            'tunjangan_thr' => ['nullable', 'numeric', 'min:0'],
            'tunjangan_kesehatan' => ['nullable', 'numeric', 'min:0'],
            'lembur_per_jam' => ['nullable', 'numeric', 'min:0'],
            'include_tunjangan' => ['nullable', 'boolean'],
            'aktif' => ['nullable', 'boolean'],
            'training_enabled' => ['nullable', 'boolean'],
            'training_start_date' => ['nullable', 'required_if:training_enabled,1', 'date'],
            'training_duration_days' => ['nullable', 'required_if:training_enabled,1', 'integer', 'min:1', 'max:365'],
            'training_deduction_type' => ['nullable', 'required_if:training_enabled,1', 'in:percentage,fixed'],
            'training_deduction_value' => ['nullable', 'required_if:training_enabled,1', 'numeric', 'min:0'],
        ]);

        $gajiPokok = (float) $request->input('gaji_pokok', 0);
        $mode = (string) ($request->input('gaji_harian_mode') ?: 'manual');
        $hariKerjaStandar = 26;

        $tunjanganUmum = (float) $request->input('tunjangan_umum', 0);
        $tunjanganTransport = (float) $request->input('tunjangan_transport', 0);
        $tunjanganThr = (float) $request->input('tunjangan_thr', 0);
        $tunjanganKesehatan = (float) $request->input('tunjangan_kesehatan', 0);
        $tunjanganTotal = $tunjanganUmum + $tunjanganTransport + $tunjanganThr + $tunjanganKesehatan;

        if ($mode === 'pokok') {
            $gajiHarian = $gajiPokok / $hariKerjaStandar;
        } elseif ($mode === 'pokok_plus_tunjangan') {
            $gajiHarian = ($gajiPokok + $tunjanganTotal) / $hariKerjaStandar;
        } else {
            $gajiHarian = (float) $request->input('gaji_harian', 0);
        }

        $trainingEnabled = $request->boolean('training_enabled');

        $salary = UserSalary::updateOrCreate(
            ['user_id' => $user->id],
            [
                'gaji_pokok' => $gajiPokok,
                'gaji_harian' => round($gajiHarian, 2),
                'gaji_harian_mode' => $mode,
                'auto_generate_harian' => $mode !== 'manual',
                'tunjangan_umum' => $tunjanganUmum,
                'tunjangan_transport' => $tunjanganTransport,
                'tunjangan_thr' => $tunjanganThr,
                'tunjangan_kesehatan' => $tunjanganKesehatan,
                'lembur_per_jam' => (float) $request->input('lembur_per_jam', 0),
                'include_tunjangan' => $request->boolean('include_tunjangan'),
                'aktif' => $request->boolean('aktif'),
                'training_enabled' => $trainingEnabled,
                'training_start_date' => $trainingEnabled ? $request->input('training_start_date') : null,
                'training_duration_days' => $trainingEnabled ? (int) $request->input('training_duration_days', 0) : 0,
                'training_deduction_type' => $trainingEnabled ? (string) ($request->input('training_deduction_type') ?: 'percentage') : 'percentage',
                'training_deduction_value' => $trainingEnabled ? (float) $request->input('training_deduction_value', 0) : 0,
            ]
        );

        return response()->json([
            'status' => 'ok',
            'message' => 'Master gaji berhasil disimpan.',
            'data' => [
                'employee_id' => (int) $user->id,
                'employee_name' => (string) ($user->name ?? '-'),
                'salary' => $salary,
            ],
        ]);
    }

    public function pay(Request $request, User $user): JsonResponse
    {
        if (!$request->user() || !$request->user()->isPanelAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak.',
            ], 403);
        }

        if (!$user->isKaryawan()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Karyawan tidak valid.',
            ], 422);
        }

        $validated = $request->validate([
            'period' => ['nullable', 'regex:/^\d{4}\-\d{2}$/'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'year' => ['nullable', 'integer', 'min:2020', 'max:2100'],
        ]);

        $periodYm = (string) ($validated['period'] ?? '');
        if ($periodYm === '') {
            $month = (int) ($validated['month'] ?? now()->month);
            $year = (int) ($validated['year'] ?? now()->year);
            $periodYm = sprintf('%04d-%02d', $year, $month);
        }

        $salary = $user->salary;
        if (!$salary || !$salary->aktif) {
            return response()->json([
                'status' => 'error',
                'message' => 'Master gaji belum aktif untuk karyawan ini.',
            ], 422);
        }

        if ($salary->isPaidFor($periodYm)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gaji periode ini sudah dibayar.',
            ], 422);
        }

        $date = Carbon::createFromFormat('Y-m', $periodYm);

        DB::transaction(function () use ($request, $user, $salary, $date, $periodYm) {
            $salary->update([
                'is_paid' => true,
                'payroll_period' => $periodYm,
                'paid_at' => now(),
                'paid_by' => $request->user()->id,
            ]);

            Absensi::query()
                ->where('user_id', $user->id)
                ->whereMonth('tanggal', $date->month)
                ->whereYear('tanggal', $date->year)
                ->update(['locked' => true]);
        });

        return response()->json([
            'status' => 'ok',
            'message' => 'Pembayaran gaji berhasil diproses.',
            'data' => [
                'employee_id' => (int) $user->id,
                'period' => $periodYm,
                'paid_at' => now()->toDateTimeString(),
            ],
        ]);
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

    private function calculatePayrollSummary(User $user, Carbon $date): array
    {
        $salary = $user->salary;
        if (!$salary || !$salary->aktif) {
            return [
                'hari_hadir' => 0,
                'hari_normal' => 0,
                'hari_tambahan' => 0,
                'hari_telat' => 0,
                'menit_telat' => 0,
                'hari_tidak_masuk' => 0,
                'gaji_per_hari' => 0.0,
                'gaji_bruto' => 0.0,
                'gaji_bonus' => 0.0,
                'tunjangan_umum' => 0.0,
                'tunjangan_transport' => 0.0,
                'tunjangan_thr' => 0.0,
                'tunjangan_kesehatan' => 0.0,
                'total_tunjangan' => 0.0,
                'total_gaji_master' => 0.0,
                'lembur' => 0.0,
                'bonus_job' => 0.0,
                'potongan_training' => 0.0,
                'total_potongan' => 0.0,
                'salary_kotor' => 0.0,
                'gaji_diterima' => 0.0,
            ];
        }

        $bulan = $date->month;
        $tahun = $date->year;
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
        $hariTambahan = max($presensi - $hariKerjaStandar, 0);
        $offDay = max($hariKerjaStandar - $hariNormal, 0);
        $menitTerlambat = (int) $absensis->where('status', 'terlambat')->sum('menit_terlambat');

        $tunjangan = [
            'tunjangan_umum' => (float) ($salary->tunjangan_umum ?? 0),
            'tunjangan_transport' => (float) ($salary->tunjangan_transport ?? 0),
            'tunjangan_thr' => (float) ($salary->tunjangan_thr ?? 0),
            'tunjangan_kesehatan' => (float) ($salary->tunjangan_kesehatan ?? 0),
        ];

        $totalTunjanganMaster = (float) array_sum($tunjangan);
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
            ->sum(fn ($l) => Carbon::parse($l->jam_mulai)->diffInMinutes(Carbon::parse($l->jam_selesai)) / 60);
        $uangLembur = (float) $totalJamLembur * (float) ($salary->lembur_per_jam ?? 0);

        $bonusJob = (float) DB::table('job_todo_user')
            ->join('job_todos', 'job_todos.id', '=', 'job_todo_user.job_todo_id')
            ->where('job_todo_user.user_id', $user->id)
            ->where('job_todo_user.status', 'completed')
            ->whereMonth('job_todo_user.completed_at', $bulan)
            ->whereYear('job_todo_user.completed_at', $tahun)
            ->sum('job_todos.bonus');

        $salaryKotor = $gajiNormal + $gajiTambahan + $uangLembur + $bonusJob + $tunjanganPayroll;

        $rules = SalaryDeductionRule::query()->where('aktif', true)->get();
        $totalPotongan = 0.0;

        foreach ($rules as $rule) {
            if (!$rule->isApplicableForPenempatan($user->penempatan)) {
                continue;
            }

            $base = match ($rule->base_source) {
                'gaji_pokok' => (float) ($salary->gaji_pokok ?? 0),
                'tunjangan' => collect($rule->tunjangan_items ?? [])->sum(fn ($item) => (float) ($tunjangan[$item] ?? 0)),
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

        $trainingInfo = $salary->calculateTrainingDeduction($salaryKotor, $date);
        $potonganTraining = (float) ($trainingInfo['deduction_nominal'] ?? 0);
        $totalPotongan += $potonganTraining;

        return [
            'hari_hadir' => $presensi,
            'hari_normal' => $hariNormal,
            'hari_tambahan' => $hariTambahan,
            'hari_telat' => $hariTelat,
            'menit_telat' => $menitTerlambat,
            'hari_tidak_masuk' => $offDay,
            'gaji_per_hari' => $gajiPerHari,
            'gaji_bruto' => $gajiNormal,
            'gaji_bonus' => $gajiTambahan,
            'tunjangan_umum' => $tunjangan['tunjangan_umum'],
            'tunjangan_transport' => $tunjangan['tunjangan_transport'],
            'tunjangan_thr' => $tunjangan['tunjangan_thr'],
            'tunjangan_kesehatan' => $tunjangan['tunjangan_kesehatan'],
            'total_tunjangan' => $totalTunjanganMaster,
            'total_gaji_master' => (float) ($salary->gaji_pokok ?? 0) + $totalTunjanganMaster,
            'lembur' => $uangLembur,
            'bonus_job' => $bonusJob,
            'potongan_training' => $potonganTraining,
            'total_potongan' => $totalPotongan,
            'salary_kotor' => $salaryKotor,
            'gaji_diterima' => max($salaryKotor - $totalPotongan, 0),
        ];
    }
}

