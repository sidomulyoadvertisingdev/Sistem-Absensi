@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">

{{-- ================= HEADER MODERN ================= --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 bg-gradient-primary">
            <div class="card-body text-white">

                <h3 class="mb-1 font-weight-bold">
                    Dashboard
                </h3>

                <small>
                    Selamat datang,
                    <strong>{{ auth()->user()->name }}</strong>
                </small>

            </div>
        </div>
    </div>
</div>

{{-- ================= KPI OVERVIEW ================= --}}
<h5 class="mb-3 font-weight-bold">
    <i class="fas fa-chart-bar mr-2"></i> KPI Overview
</h5>

<div class="row">

<div class="col-lg-3 col-6">
<div class="small-box bg-info shadow">
<div class="inner">
<h3>{{ $totalAbsensi }}</h3>
<p>Total Absensi</p>
</div>
<div class="icon"><i class="fas fa-user-check"></i></div>
<a href="{{ route('admin.absensi') }}" class="small-box-footer">
Detail <i class="fas fa-arrow-circle-right"></i>
</a>
</div>
</div>

<div class="col-lg-3 col-6">
<div class="small-box bg-warning shadow">
<div class="inner">
<h3>{{ $totalLembur }}</h3>
<p>Total Lembur</p>
</div>
<div class="icon"><i class="fas fa-clock"></i></div>
<a href="{{ route('admin.lembur') }}" class="small-box-footer">
Detail <i class="fas fa-arrow-circle-right"></i>
</a>
</div>
</div>

<div class="col-lg-3 col-6">
<div class="small-box bg-success shadow">
<div class="inner">
<h3>{{ $totalGaji }}</h3>
<p>Karyawan Bergaji</p>
</div>
<div class="icon"><i class="fas fa-money-bill-wave"></i></div>
<a href="{{ route('admin.gaji') }}" class="small-box-footer">
Detail <i class="fas fa-arrow-circle-right"></i>
</a>
</div>
</div>

<div class="col-lg-3 col-6">
<div class="small-box bg-secondary shadow">
<div class="inner">
<h3>{{ $totalUser }}</h3>
<p>Total Karyawan</p>
</div>
<div class="icon"><i class="fas fa-users"></i></div>
<a href="{{ route('admin.karyawan.index') }}" class="small-box-footer">
Detail <i class="fas fa-arrow-circle-right"></i>
</a>
</div>
</div>

</div>

{{-- ================= MONITORING REKRUTMEN ================= --}}
<h5 class="mt-4 mb-3 font-weight-bold">
<i class="fas fa-briefcase mr-2"></i> Monitoring Rekrutmen
</h5>

<div class="row text-center">

@php
$rekrut = [
['bg'=>'warning','val'=>$pelamarPending,'label'=>'Pending'],
['bg'=>'info','val'=>$pelamarReview,'label'=>'Review'],
['bg'=>'primary','val'=>$pelamarInterview,'label'=>'Interview'],
['bg'=>'teal','val'=>$pelamarTraining,'label'=>'Training'],
['bg'=>'success','val'=>$pelamarAccepted,'label'=>'Diterima'],
['bg'=>'danger','val'=>$pelamarRejected,'label'=>'Ditolak'],
];
@endphp

@foreach($rekrut as $r)
<div class="col-lg-2 col-6">
<div class="small-box bg-{{ $r['bg'] }} shadow-sm">
<div class="inner">
<h3>{{ $r['val'] }}</h3>
<p>{{ $r['label'] }}</p>
</div>
</div>
</div>
@endforeach

</div>

{{-- ================= JOB TODO ================= --}}
<h5 class="mt-4 mb-3 font-weight-bold">
<i class="fas fa-tasks mr-2"></i> Job Todo
</h5>

<div class="row">

<div class="col-lg-3 col-6">
<div class="small-box bg-info shadow">
<div class="inner"><h3>{{ $jobTotal }}</h3><p>Total Job</p></div>
<div class="icon"><i class="fas fa-clipboard-list"></i></div>
</div>
</div>

<div class="col-lg-3 col-6">
<div class="small-box bg-warning shadow">
<div class="inner"><h3>{{ $jobOpen }}</h3><p>Job Open</p></div>
<div class="icon"><i class="fas fa-folder-open"></i></div>
</div>
</div>

<div class="col-lg-3 col-6">
<div class="small-box bg-primary shadow">
<div class="inner"><h3>{{ $jobSedangDikerjakan }}</h3><p>Dikerjakan</p></div>
<div class="icon"><i class="fas fa-spinner"></i></div>
</div>
</div>

<div class="col-lg-3 col-6">
<div class="small-box bg-success shadow">
<div class="inner"><h3>{{ $jobSelesai }}</h3><p>Selesai</p></div>
<div class="icon"><i class="fas fa-check-circle"></i></div>
</div>
</div>

<div class="col-12 mt-3">
<div class="small-box bg-secondary shadow text-center">
<div class="inner">
<h4>{{ $jobClosed }}</h4>
<p>Job Closed</p>
</div>
</div>
</div>

</div>

{{-- ================= PENEMPATAN ================= --}}
<h5 class="mt-4 mb-3 font-weight-bold">
<i class="fas fa-building mr-2"></i> Distribusi Karyawan
</h5>

<div class="row">

<div class="col-lg-4">
<div class="small-box bg-primary shadow">
<div class="inner"><h3>{{ $karyawanSMLeccy }}</h3><p>SM Lecy</p></div>
<div class="icon"><i class="fas fa-industry"></i></div>
</div>
</div>

<div class="col-lg-4">
<div class="small-box bg-dark shadow">
<div class="inner"><h3>{{ $karyawanGudang }}</h3><p>Gudang</p></div>
<div class="icon"><i class="fas fa-warehouse"></i></div>
</div>
</div>

<div class="col-lg-4">
<div class="small-box bg-teal shadow">
<div class="inner"><h3>{{ $karyawanSMPenempatan }}</h3><p>Percetakan</p></div>
<div class="icon"><i class="fas fa-print"></i></div>
</div>
</div>

</div>

{{-- ================= DATA TERBARU ================= --}}
<div class="row mt-4">

{{-- ABSENSI --}}
<div class="col-md-6">
<div class="card shadow-sm">
<div class="card-header bg-info text-white">
Absensi Terbaru
</div>
<div class="card-body p-0">
<table class="table table-striped mb-0">
<thead>
<tr><th>Nama</th><th>Jam</th><th>Aksi</th><th>Status</th></tr>
</thead>
<tbody>
@forelse ($absensiTerbaru as $item)
<tr>
<td>{{ $item->user->name ?? '-' }}</td>
<td>{{ $item->jam_tampil }}</td>
<td><span class="badge badge-{{ $item->aksi_badge }}">{{ $item->aksi_label }}</span></td>
<td><span class="badge badge-{{ $item->status_badge }}">{{ $item->status_label }}</span></td>
</tr>
@empty
<tr><td colspan="4" class="text-center text-muted">Kosong</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
</div>

{{-- LEMBUR --}}
<div class="col-md-6">
<div class="card shadow-sm">
<div class="card-header bg-warning">
Lembur Terbaru
</div>
<div class="card-body p-0">
<table class="table table-striped mb-0">
<thead>
<tr><th>Nama</th><th>Tanggal</th><th>Status</th></tr>
</thead>
<tbody>
@forelse ($lemburTerbaru as $item)
<tr>
<td>{{ $item->user->name ?? '-' }}</td>
<td>{{ $item->tanggal }}</td>
<td><span class="badge badge-warning">{{ ucfirst($item->status) }}</span></td>
</tr>
@empty
<tr><td colspan="3" class="text-center text-muted">Kosong</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
</div>

</div>

</div>
@endsection
