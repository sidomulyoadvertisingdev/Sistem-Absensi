@extends('layouts.app')

@section('title', 'Detail Gaji Karyawan')

@section('content')
<div class="container-fluid">

{{-- ================= HEADER ================= --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        Detail Gaji - {{ $user->name }}
        <small class="text-muted">({{ $periode }})</small>
    </h4>

    <div class="d-flex align-items-center gap-2">

        <a href="{{ route('admin.gaji.slip.pdf', [$user->id, 'bulan' => $bulan]) }}"
           target="_blank"
           class="btn btn-outline-secondary">
            Export Slip
        </a>

        @if(!$isPaid)
        <form method="POST"
              action="{{ route('admin.gaji.pay', [$user->id, 'bulan' => $bulan]) }}"
              onsubmit="return confirm('Bayar gaji dan kunci absensi?')">
            @csrf
            <button type="submit" class="btn btn-success">
                Bayar Gaji
            </button>
        </form>
        @else
        <span class="badge badge-success p-2">
            Sudah Dibayar
        </span>
        @endif

    </div>
</div>

{{-- ================= INFO PEMBAYARAN ================= --}}
@if($isPaid)
<div class="alert alert-success">
    <strong>Pembayaran:</strong><br>
    Tanggal: {{ optional($salary->paid_at)->format('d M Y H:i') }}<br>
    Oleh: {{ optional($salary->payer)->name ?? 'Admin' }}
</div>
@endif

{{-- ================= SUMMARY ================= --}}
<div class="row mb-4">

@php
$cards = [
    ['Hari Hadir',$hariHadir,'primary'],
    ['Terlambat',$hariTelat,'warning'],
    ['Menit Telat',$menitTerlambat,'danger'],
];
@endphp

@foreach($cards as [$title,$value,$color])
<div class="col-md-3">
<div class="card text-center border-{{ $color }}">
<div class="card-body">
<small>{{ $title }}</small>
<h4>{{ $value }}</h4>
</div>
</div>
</div>
@endforeach

<div class="col-md-3">
<div class="card text-center border-success">
<div class="card-body">
<small>Total Diterima</small>
<h4 class="text-success">
Rp {{ number_format($totalGaji,0,',','.') }}
</h4>
</div>
</div>
</div>

</div>

{{-- ================= ABSENSI ================= --}}
<div class="card mb-4">
<div class="card-header"><strong>Detail Absensi</strong></div>
<div class="card-body table-responsive p-0">

<table class="table table-bordered table-sm mb-0">

<thead>
<tr>
<th>Tanggal</th>
<th>Masuk</th>
<th>Pulang</th>
<th>Status</th>
<th>Telat</th>
</tr>
</thead>

<tbody>
@forelse($absensis as $a)
<tr>
<td>{{ \Carbon\Carbon::parse($a->tanggal)->format('d M Y') }}</td>
<td>{{ $a->jam_masuk ?? '-' }}</td>
<td>{{ $a->jam_pulang ?? '-' }}</td>
<td>
<span class="badge badge-{{ $a->status === 'terlambat' ? 'danger' : 'success' }}">
{{ ucfirst($a->status) }}
</span>
</td>
<td>{{ $a->menit_terlambat }}</td>
</tr>
@empty
<tr>
<td colspan="5" class="text-center text-muted">
Tidak ada data
</td>
</tr>
@endforelse
</tbody>

</table>
</div>
</div>

{{-- ================= BONUS ================= --}}
<div class="card mb-4">
<div class="card-header"><strong>Bonus Job</strong></div>
<div class="card-body table-responsive p-0">

<table class="table table-bordered table-sm mb-0">

<thead>
<tr>
<th>Job</th>
<th>Bonus</th>
</tr>
</thead>

<tbody>
@forelse($jobBonus as $job)
<tr>
<td>{{ $job->title }}</td>
<td>Rp {{ number_format($job->bonus,0,',','.') }}</td>
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
</div>
</div>

{{-- ================= RINCIAN GAJI ================= --}}
<div class="card mb-4">
<div class="card-header"><strong>Rincian Perhitungan Gaji</strong></div>
<div class="card-body">

<table class="table table-bordered">

<tr>
<th>Gaji Harian</th>
<td>Rp {{ number_format($gajiPerHari,0,',','.') }}</td>
</tr>

<tr>
<th>Gaji Dasar ({{ $hariKerjaMasuk ?? ($hariHadir + $hariTelat) }} hari kerja masuk)</th>
<td>Rp {{ number_format($gajiBruto,0,',','.') }}</td>
</tr>

<tr>
<th>Total Tunjangan</th>
<td>
Rp {{ number_format($totalTunjanganMaster,0,',','.') }}

@if(!$salary->include_tunjangan)
<br>
<small class="text-muted">
(Tidak dihitung ke payroll)
</small>
@endif
</td>
</tr>

<tr>
<th>Lembur</th>
<td>Rp {{ number_format($uangLembur,0,',','.') }}</td>
</tr>

<tr>
<th>Bonus</th>
<td>Rp {{ number_format($totalBonusJob,0,',','.') }}</td>
</tr>

<tr>
<th>Gaji Kotor</th>
<td>Rp {{ number_format($salaryKotor,0,',','.') }}</td>
</tr>

{{-- ===== POTONGAN — SAMA DENGAN LAPORAN ===== --}}
<tr>
<th>Potongan Terlambat</th>
<td class="text-danger">
Rp {{ number_format($potonganTelatNominal ?? 0,0,',','.') }}
</td>
</tr>

<tr>
<th>Potongan Training</th>
<td class="text-danger">
Rp {{ number_format($potonganTrainingNominal ?? 0,0,',','.') }}
@if(($trainingInfo['active'] ?? false))
<br>
<small class="text-muted">
Aktif {{ $trainingInfo['overlap_days'] ?? 0 }} hari, potongan/hari Rp {{ number_format($trainingInfo['deduction_per_day'] ?? 0,0,',','.') }}
</small>
@endif
</td>
</tr>

<tr>
<th>Total Potongan</th>
<td class="text-danger font-weight-bold">
Rp {{ number_format($totalPotongan,0,',','.') }}
</td>
</tr>

<tr class="table-success">
<th>Total Diterima</th>
<td class="font-weight-bold">
Rp {{ number_format($totalGaji,0,',','.') }}
</td>
</tr>

</table>

</div>
</div>

</div>
@endsection
