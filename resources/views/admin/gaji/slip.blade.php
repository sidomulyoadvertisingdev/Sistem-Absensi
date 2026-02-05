@extends('layouts.app')

@section('title', 'Slip Gaji')

@section('content')
<div class="container-fluid">

@php
/*
|--------------------------------------------------------------------------
| SAFE VARIABLE FALLBACK
|--------------------------------------------------------------------------
| Controller bisa kirim nama beda — kita amankan di sini
*/
$gajiFix = $gajiBruto ?? $gajiDasar ?? $gajiProrata ?? 0;
@endphp

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Slip Gaji</h1>

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
<th>Gaji Harian</th>
<td class="text-right">
Rp {{ number_format($gajiPerHari ?? 0,0,',','.') }}
</td>
</tr>

<tr>
<th>Gaji Dasar ({{ $hariHadir ?? 0 }} hari)</th>
<td class="text-right">
Rp {{ number_format($gajiFix,0,',','.') }}
</td>
</tr>

<tr>
<th>Tunjangan Umum</th>
<td class="text-right">
Rp {{ number_format($salary->tunjangan_umum ?? 0,0,',','.') }}
</td>
</tr>

<tr>
<th>Tunjangan Transport</th>
<td class="text-right">
Rp {{ number_format($salary->tunjangan_transport ?? 0,0,',','.') }}
</td>
</tr>

<tr>
<th>Tunjangan THR</th>
<td class="text-right">
Rp {{ number_format($salary->tunjangan_thr ?? 0,0,',','.') }}
</td>
</tr>

<tr>
<th>Tunjangan Kesehatan</th>
<td class="text-right">
Rp {{ number_format($salary->tunjangan_kesehatan ?? 0,0,',','.') }}
</td>
</tr>

@if(empty($salary->include_tunjangan))
<tr>
<td colspan="2" class="text-muted small">
*Tunjangan hanya informasi dan tidak masuk total gaji
</td>
</tr>
@endif

<tr>
<th>Lembur ({{ number_format($totalJamLembur ?? 0,1) }} jam)</th>
<td class="text-right">
Rp {{ number_format($uangLembur ?? 0,0,',','.') }}
</td>
</tr>

<tr>
<th>Bonus Job</th>
<td class="text-right">
Rp {{ number_format($totalBonusJob ?? 0,0,',','.') }}
</td>
</tr>

<tr class="bg-light">
<th>Salary Kotor</th>
<td class="text-right">
Rp {{ number_format($salaryKotor ?? 0,0,',','.') }}
</td>
</tr>

<tr>
<th class="text-danger">Potongan</th>
<td class="text-right text-danger">
Rp {{ number_format($totalPotongan ?? 0,0,',','.') }}
</td>
</tr>

<tr class="table-success">
<th><strong>Total Diterima</strong></th>
<td class="text-right">
<strong>
Rp {{ number_format($totalGaji ?? 0,0,',','.') }}
</strong>
</td>
</tr>

</table>

{{-- BONUS DETAIL --}}
<h5 class="mt-4">Rincian Bonus Job</h5>

<table class="table table-sm table-bordered">
<thead>
<tr>
<th>Job</th>
<th class="text-right">Bonus</th>
</tr>
</thead>

<tbody>
@forelse($jobBonus ?? [] as $job)
<tr>
<td>{{ $job->title }}</td>
<td class="text-right">
Rp {{ number_format($job->bonus,0,',','.') }}
</td>
</tr>
@empty
<tr>
<td colspan="2" class="text-center text-muted">
Tidak ada bonus
</td>
</tr>
@endforelse
</tbody>
</table>

{{-- ACTION --}}
<div class="mt-3">

<a href="{{ route('admin.gaji') }}"
   class="btn btn-secondary">
← Kembali
</a>

<a href="{{ route('admin.gaji.slip.pdf',$user->id) }}?bulan={{ $bulan }}"
   class="btn btn-danger ml-2">
Cetak PDF
</a>

</div>

</div>
</div>

</div>
@endsection
