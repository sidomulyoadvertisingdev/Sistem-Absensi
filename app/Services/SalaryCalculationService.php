<?php

namespace App\Services;

use App\Models\SalaryDeductionRule;
use App\Models\Employee;

class SalaryCalculationService
{
    public function calculate(Employee $employee): array
    {
        $rules = SalaryDeductionRule::where('aktif', true)->get();

        $gajiPokok  = $employee->gaji_pokok;
        $tunjangan  = $employee->tunjangan ?? [];
        $totalTunjangan = array_sum($tunjangan);
        $totalGaji = $gajiPokok + $totalTunjangan;

        $potongan = [
            'gaji_pokok' => 0,
            'tunjangan'  => 0,
            'total_gaji' => 0,
        ];

        $detail = [];

        foreach ($rules as $rule) {

            // 🔥 FILTER PENEMPATAN (WAJIB)
            if (!$rule->isApplicableForPenempatan($employee->penempatan)) {
                continue;
            }

            // 🔥 HITUNG SESUAI BASE
            if ($rule->isFromTotalGaji()) {
                $nilai = $rule->calculate($totalGaji);
                $potongan['total_gaji'] += $nilai;
            }
            elseif ($rule->isFromTunjangan()) {
                $nilai = $rule->calculateFromTunjangan($tunjangan);
                $potongan['tunjangan'] += $nilai;
            }
            else {
                $nilai = $rule->calculate($gajiPokok);
                $potongan['gaji_pokok'] += $nilai;
            }

            if ($nilai > 0) {
                $detail[] = [
                    'kode'        => $rule->kode,
                    'nama'        => $rule->nama,
                    'base_source' => $rule->base_source,
                    'penempatan'  => implode(', ', $rule->penempatan),
                    'nilai'       => $nilai,
                ];
            }
        }

        $totalPotongan =
            $potongan['gaji_pokok']
            + $potongan['tunjangan']
            + $potongan['total_gaji'];

        return [
            'penempatan'      => $employee->penempatan,

            'gaji_pokok'      => $gajiPokok,
            'total_tunjangan' => $totalTunjangan,
            'total_gaji'      => $totalGaji,

            'potongan'        => $potongan,
            'total_potongan'  => $totalPotongan,

            'gaji_bersih'     => $totalGaji - $totalPotongan,

            'detail'          => $detail,
        ];
    }
}
