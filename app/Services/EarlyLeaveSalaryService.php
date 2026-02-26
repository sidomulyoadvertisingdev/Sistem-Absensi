<?php

namespace App\Services;

use App\Models\Submission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EarlyLeaveSalaryService
{
    public function calculate(
        User $user,
        Collection $absensis,
        float $gajiPerHari,
        int $bulan,
        int $tahun,
        int $hariKerjaStandar = 26
    ): array {
        $presentAbsensis = $absensis->filter(
            fn ($absensi) => in_array((string) $absensi->status, ['hadir', 'terlambat'], true)
        )->values();

        $hariKerjaMasuk = (float) $presentAbsensis->count();
        $hariKerjaSetara = $hariKerjaMasuk;
        $jumlahIzinPulangAwal = 0;
        $menitKerjaIzinPulangAwal = 0;
        $potonganIzinPulangAwal = 0.0;

        if ($hariKerjaMasuk > 0) {
            $approvedByDate = $this->getApprovedEarlyLeaveByDate($user, $bulan, $tahun);

            foreach ($presentAbsensis as $absensi) {
                $tanggal = Carbon::parse($absensi->tanggal)->toDateString();

                if (!$approvedByDate->has($tanggal)) {
                    continue;
                }

                $approvalAt = $approvedByDate->get($tanggal);
                $dayCalculation = $this->calculateDayRatio($user, $absensi, $approvalAt);

                if (!$dayCalculation['applied']) {
                    continue;
                }

                $ratio = (float) $dayCalculation['ratio'];
                $hariKerjaSetara += ($ratio - 1);
                $jumlahIzinPulangAwal++;
                $menitKerjaIzinPulangAwal += (int) $dayCalculation['worked_minutes'];
                $potonganIzinPulangAwal += $gajiPerHari * (1 - $ratio);
            }
        }

        $hariKerjaSetara = max($hariKerjaSetara, 0);
        $hariNormalSetara = min($hariKerjaSetara, $hariKerjaStandar);
        $hariTambahanSetara = max($hariKerjaSetara - $hariKerjaStandar, 0);

        $gajiNormal = $gajiPerHari * $hariNormalSetara;
        $gajiTambahan = $gajiPerHari * $hariTambahanSetara;
        $gajiBruto = $gajiPerHari * $hariKerjaSetara;

        return [
            'hari_kerja_setara' => $hariKerjaSetara,
            'hari_normal_setara' => $hariNormalSetara,
            'hari_tambahan_setara' => $hariTambahanSetara,
            'gaji_normal' => $gajiNormal,
            'gaji_tambahan' => $gajiTambahan,
            'gaji_bruto' => $gajiBruto,
            'jumlah_izin_pulang_awal' => $jumlahIzinPulangAwal,
            'menit_kerja_izin_pulang_awal' => $menitKerjaIzinPulangAwal,
            'potongan_izin_pulang_awal' => $potonganIzinPulangAwal,
        ];
    }

    private function getApprovedEarlyLeaveByDate(User $user, int $bulan, int $tahun): Collection
    {
        return Submission::query()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereNotNull('approved_at')
            ->whereMonth('approved_at', $bulan)
            ->whereYear('approved_at', $tahun)
            ->whereHas('type', fn ($q) => $q->where('is_izin_pulang_awal', true))
            ->orderBy('approved_at')
            ->get(['approved_at'])
            ->groupBy(fn ($submission) => Carbon::parse($submission->approved_at)->toDateString())
            ->map(fn (Collection $items) => Carbon::parse($items->last()->approved_at));
    }

    private function calculateDayRatio(User $user, object $absensi, Carbon $approvalAt): array
    {
        $tanggal = Carbon::parse($absensi->tanggal)->toDateString();
        $jamMasuk = $this->safeDateTime($tanggal, (string) ($absensi->jam_masuk ?? ''));

        if (!$jamMasuk) {
            return [
                'applied' => false,
                'ratio' => 1.0,
                'worked_minutes' => 0,
            ];
        }

        $jamPulang = $this->safeDateTime($tanggal, (string) ($absensi->jam_pulang ?? ''));

        $batasKerjaAktual = $approvalAt->copy();
        if ($jamPulang && $jamPulang->lt($batasKerjaAktual)) {
            $batasKerjaAktual = $jamPulang;
        }

        if ($batasKerjaAktual->lte($jamMasuk)) {
            return [
                'applied' => true,
                'ratio' => 0.0,
                'worked_minutes' => 0,
            ];
        }

        $jadwal = $user->resolveWorkSchedule($tanggal);
        $jadwalMasuk = $this->safeDateTime($tanggal, (string) ($jadwal->jam_masuk ?? ''));
        $jadwalPulang = $this->safeDateTime($tanggal, (string) ($jadwal->jam_pulang ?? ''));
        $jadwalIstirahatMulai = $this->safeDateTime($tanggal, (string) ($jadwal->istirahat_mulai ?? ''));
        $jadwalIstirahatSelesai = $this->safeDateTime($tanggal, (string) ($jadwal->istirahat_selesai ?? ''));

        $scheduledMinutes = 8 * 60;
        if ($jadwalMasuk && $jadwalPulang && $jadwalPulang->gt($jadwalMasuk)) {
            $scheduledMinutes = $jadwalMasuk->diffInMinutes($jadwalPulang);

            if ($jadwalIstirahatMulai && $jadwalIstirahatSelesai && $jadwalIstirahatSelesai->gt($jadwalIstirahatMulai)) {
                $scheduledMinutes -= $jadwalIstirahatMulai->diffInMinutes($jadwalIstirahatSelesai);
            }

            $scheduledMinutes = max($scheduledMinutes, 1);
        }

        $workedMinutes = $jamMasuk->diffInMinutes($batasKerjaAktual);
        $ratio = min(max($workedMinutes / $scheduledMinutes, 0), 1);

        return [
            'applied' => true,
            'ratio' => $ratio,
            'worked_minutes' => $workedMinutes,
        ];
    }

    private function safeDateTime(string $tanggal, string $jam): ?Carbon
    {
        $time = trim($jam);
        if ($time === '') {
            return null;
        }

        try {
            return Carbon::parse($tanggal . ' ' . $time);
        } catch (\Exception $e) {
            return null;
        }
    }
}
