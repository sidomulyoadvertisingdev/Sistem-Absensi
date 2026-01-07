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
                    <th>Gaji Pokok</th>
                    <td class="text-right">
                        Rp {{ number_format($salary->gaji_pokok, 0, ',', '.') }}
                    </td>
                </tr>

                <tr>
                    <th>Uang Makan</th>
                    <td class="text-right">
                        Rp {{ number_format($salary->uang_makan, 0, ',', '.') }}
                    </td>
                </tr>

                <tr>
                    <th>Transport</th>
                    <td class="text-right">
                        Rp {{ number_format($salary->transport, 0, ',', '.') }}
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
                        Rp {{ number_format($totalBonusJob ?? 0, 0, ',', '.') }}
                    </td>
                </tr>

                <tr class="bg-light">
                    <th><strong>TOTAL GAJI</strong></th>
                    <th class="text-right">
                        Rp {{ number_format($totalGaji, 0, ',', '.') }}
                    </th>
                </tr>
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
