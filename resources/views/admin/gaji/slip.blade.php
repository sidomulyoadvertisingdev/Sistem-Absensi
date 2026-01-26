@extends('layouts.app')

@section('title', 'Slip Gaji')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Slip Gaji</h1>

        {{-- FILTER BULAN --}}
        <form method="GET" class="form-inline">
            <input type="month"
                   name="bulan"
                   value="{{ $bulan }}"
                   class="form-control form-control-sm mr-2">

            <button class="btn btn-primary btn-sm">
                Tampilkan
            </button>
        </form>
    </div>

    <div class="card">
        <div class="card-body">

            {{-- INFO KARYAWAN --}}
            <table class="table table-borderless mb-3">
                <tr>
                    <th width="200">Nama</th>
                    <td>{{ $user->name }}</td>
                </tr>
                <tr>
                    <th>NIK</th>
                    <td>{{ $user->nik ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Jabatan</th>
                    <td>{{ $user->jabatan ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Bulan</th>
                    <td>
                        {{ \Carbon\Carbon::parse($bulan)->translatedFormat('F Y') }}
                    </td>
                </tr>
            </table>

            <hr>

            {{-- RINCIAN GAJI --}}
            <table class="table table-bordered">

                <tr>
                    <th>Gaji Pokok (Bulanan)</th>
                    <td class="text-right">
                        Rp {{ number_format($salary->gaji_pokok, 0, ',', '.') }}
                    </td>
                </tr>

                <tr>
                    <th>Hitungan Per Hari</th>
                    <td class="text-right">
                        Rp {{ number_format($gajiPerHari, 0, ',', '.') }}
                    </td>
                </tr>

                <tr>
                    <th>Gaji Pokok Dibayar ({{ $hariHadir }} Hari)</th>
                    <td class="text-right">
                        Rp {{ number_format($gajiPokokFix, 0, ',', '.') }}
                    </td>
                </tr>

                <tr>
                    <th>Tunjangan Umum</th>
                    <td class="text-right">
                        Rp {{ number_format($salary->tunjangan_umum, 0, ',', '.') }}
                    </td>
                </tr>

                <tr>
                    <th>Tunjangan Transport</th>
                    <td class="text-right">
                        Rp {{ number_format($salary->tunjangan_transport, 0, ',', '.') }}
                    </td>
                </tr>

                <tr>
                    <th>Tunjangan Hari Raya</th>
                    <td class="text-right">
                        Rp {{ number_format($salary->tunjangan_thr, 0, ',', '.') }}
                    </td>
                </tr>

                <tr>
                    <th>Tunjangan Kesehatan</th>
                    <td class="text-right">
                        Rp {{ number_format($salary->tunjangan_kesehatan, 0, ',', '.') }}
                    </td>
                </tr>

                <tr>
                    <th>Lembur ({{ number_format($totalJamLembur, 1) }} Jam)</th>
                    <td class="text-right">
                        Rp {{ number_format($totalLembur, 0, ',', '.') }}
                    </td>
                </tr>

                {{-- 🔥 BONUS JOB TODO --}}
                <tr>
                    <th>Bonus Job Todo</th>
                    <td class="text-right">
                        Rp {{ number_format($totalBonusJob, 0, ',', '.') }}
                    </td>
                </tr>

                <tr class="bg-light">
                    <th><strong>TOTAL GAJI</strong></th>
                    <th class="text-right">
                        Rp {{ number_format($totalGaji, 0, ',', '.') }}
                    </th>
                </tr>
            </table>

            {{-- RINCIAN BONUS JOB --}}
            <h5 class="mt-4">Rincian Bonus Job Todo</h5>
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Job</th>
                        <th class="text-right">Bonus</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($jobBonus as $job)
                    <tr>
                        <td>{{ $job->title }}</td>
                        <td class="text-right">
                            Rp {{ number_format($job->bonus, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center text-muted">
                            Tidak ada bonus job
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            {{-- ACTION --}}
            <div class="mt-3">
                <a href="{{ route('admin.gaji') }}" class="btn btn-secondary">
                    ← Kembali
                </a>

                <a href="{{ route('admin.gaji.slip.pdf', $user->id) }}?bulan={{ $bulan }}"
                   class="btn btn-danger ml-2">
                    <i class="fas fa-file-pdf"></i> Cetak PDF
                </a>
            </div>

        </div>
    </div>

</div>
@endsection
