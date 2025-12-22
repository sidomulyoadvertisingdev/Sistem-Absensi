@extends('layouts.app')

@section('title', 'Slip Gaji')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between mb-3">
        <h1>Slip Gaji</h1>

        <form method="GET">
            <input type="month" name="bulan" value="{{ $bulan }}">
            <button class="btn btn-primary btn-sm">Tampilkan</button>
        </form>
    </div>

    <div class="card">
        <div class="card-body">

            <table class="table table-borderless">
                <tr>
                    <th width="200">Nama</th>
                    <td>{{ $user->name }}</td>
                </tr>
                <tr>
                    <th>NIK</th>
                    <td>{{ $user->nik }}</td>
                </tr>
                <tr>
                    <th>Jabatan</th>
                    <td>{{ $user->jabatan }}</td>
                </tr>
                <tr>
                    <th>Bulan</th>
                    <td>{{ \Carbon\Carbon::parse($bulan)->format('F Y') }}</td>
                </tr>
            </table>

            <hr>

            <table class="table table-bordered">
                <tr>
                    <th>Gaji Pokok</th>
                    <td class="text-right">Rp {{ number_format($salary->gaji_pokok) }}</td>
                </tr>
                <tr>
                    <th>Uang Makan</th>
                    <td class="text-right">Rp {{ number_format($salary->uang_makan) }}</td>
                </tr>
                <tr>
                    <th>Transport</th>
                    <td class="text-right">Rp {{ number_format($salary->transport) }}</td>
                </tr>
                <tr>
                    <th>Lembur ({{ number_format($totalJamLembur, 1) }} Jam)</th>
                    <td class="text-right">
                        Rp {{ number_format($totalLembur) }}
                    </td>
                </tr>
                <tr class="bg-light">
                    <th><strong>TOTAL GAJI</strong></th>
                    <th class="text-right">
                        Rp {{ number_format($totalGaji) }}
                    </th>
                </tr>
            </table>

            <a href="{{ route('admin.gaji') }}" class="btn btn-secondary">
                Kembali
            </a>
            <a href="{{ route('admin.gaji.slip.pdf', $user->id) }}?bulan={{ $bulan }}"
   class="btn btn-danger">
    <i class="fas fa-file-pdf"></i> Cetak PDF
</a>


        </div>
    </div>

</div>
@endsection
