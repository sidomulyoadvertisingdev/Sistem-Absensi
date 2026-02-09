@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid dashboard-modern">

{{-- ================= HEADER MODERN ================= --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 dashboard-hero">
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
<h5 class="mb-3 font-weight-bold section-title">
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

{{-- ================= DISIPLIN KARYAWAN ================= --}}
<div class="d-flex flex-wrap justify-content-between align-items-end mt-4 mb-3">
<h5 class="mb-2 mb-md-0 font-weight-bold section-title">
<i class="fas fa-user-clock mr-2"></i> Disiplin Karyawan ({{ ucfirst($periodeAbsensiLabel) }})
</h5>

<form method="GET" action="{{ route('admin.dashboard') }}" class="d-flex align-items-end dashboard-filter-form">
<div class="mr-2">
<label for="bulan" class="small text-muted mb-1 d-block">Filter Bulan</label>
<input type="month"
       id="bulan"
       name="bulan"
       value="{{ $selectedBulan }}"
       class="form-control form-control-sm">
</div>
<button type="submit" class="btn btn-sm btn-primary btn-filter mr-2 mb-1">
<i class="fas fa-filter mr-1"></i> Tampilkan
</button>
<a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-light btn-reset border mb-1">
Reset
</a>
</form>
</div>

<div class="row">

<div class="col-lg-3 col-sm-6">
<div class="card discipline-card card-late border-0 shadow-sm h-100">
<div class="card-body">
<div class="d-flex justify-content-between align-items-start">
<div>
<small class="text-uppercase text-muted font-weight-bold">Karyawan Terlambat</small>
<h3 class="mb-0">{{ $karyawanTerlambat }}</h3>
<small class="text-muted">Pernah telat minimal 1x</small>
</div>
<span class="badge badge-warning">Telat</span>
</div>
</div>
</div>
</div>

<div class="col-lg-3 col-sm-6">
<div class="card discipline-card card-late-total border-0 shadow-sm h-100">
<div class="card-body">
<small class="text-uppercase text-muted font-weight-bold">Total Keterlambatan</small>
<h3 class="mb-0">{{ $totalKeterlambatan }}x</h3>
<small class="text-muted">Akumulasi {{ number_format((float) $totalMenitTerlambat, 0, ',', '.') }} menit telat</small>
</div>
</div>
</div>

<div class="col-lg-3 col-sm-6">
<div class="card discipline-card card-ontime border-0 shadow-sm h-100">
<div class="card-body">
<small class="text-uppercase text-muted font-weight-bold">Tidak Pernah Terlambat</small>
<h3 class="mb-0">{{ $karyawanTidakPernahTerlambat }}</h3>
<small class="text-muted">Dari total {{ $totalUser }} karyawan</small>
</div>
</div>
</div>

<div class="col-lg-3 col-sm-6">
<div class="card discipline-card card-absent border-0 shadow-sm h-100">
<div class="card-body">
<small class="text-uppercase text-muted font-weight-bold">Karyawan Tidak Masuk</small>
<h3 class="mb-0">{{ $karyawanTidakMasuk }}</h3>
<small class="text-muted">Total hari tidak masuk {{ $totalTidakMasuk }} hari (basis 26 hari kerja)</small>
</div>
</div>
</div>

</div>

<div class="row mt-2">

<div class="col-lg-6">
<div class="card shadow-sm border-0">
<div class="card-header bg-white border-0 pb-0">
<h6 class="mb-0 font-weight-bold">Perbandingan Karyawan</h6>
<small class="text-muted">Terlambat vs Tidak Pernah Terlambat</small>
</div>
<div class="card-body">
<canvas id="disciplineComparisonChart" height="220"></canvas>
</div>
</div>
</div>

<div class="col-lg-6">
<div class="card shadow-sm border-0">
<div class="card-header bg-white border-0 pb-0">
<h6 class="mb-0 font-weight-bold">Kejadian Absensi</h6>
<small class="text-muted">Hadir, Terlambat, Tidak Masuk ({{ ucfirst($periodeAbsensiLabel) }})</small>
</div>
<div class="card-body">
<canvas id="attendanceEventChart" height="220"></canvas>
</div>
</div>
</div>

</div>

<div class="row mt-2">
<div class="col-12">
<div class="card shadow-sm border-0">
<div class="card-header bg-white">
<h6 class="mb-0 font-weight-bold">
<i class="fas fa-trophy text-warning mr-1"></i>
Top 5 Karyawan Paling Sering Terlambat
</h6>
</div>
<div class="card-body p-0">
<table class="table table-hover mb-0">
<thead class="thead-light">
<tr>
<th style="width:80px;">No</th>
<th>Nama</th>
<th style="width:200px;">Jumlah Telat</th>
<th style="width:220px;">Akumulasi Menit</th>
</tr>
</thead>
<tbody>
@forelse($topKaryawanTerlambat as $index => $item)
<tr>
<td>{{ $index + 1 }}</td>
<td>{{ $item->user->name ?? '-' }}</td>
<td><span class="badge badge-warning">{{ (int) $item->total_terlambat }}x</span></td>
<td>{{ number_format((float) $item->total_menit, 0, ',', '.') }} menit</td>
</tr>
@empty
<tr>
<td colspan="4" class="text-center text-muted py-4">
Belum ada data keterlambatan pada periode ini
</td>
</tr>
@endforelse
</tbody>
</table>
</div>
</div>
</div>
</div>

{{-- ================= MONITORING REKRUTMEN ================= --}}
<h5 class="mt-4 mb-3 font-weight-bold section-title">
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
<h5 class="mt-4 mb-3 font-weight-bold section-title">
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
<h5 class="mt-4 mb-3 font-weight-bold section-title">
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
Absensi Terbaru ({{ ucfirst($periodeAbsensiLabel) }})
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
Lembur Terbaru ({{ ucfirst($periodeAbsensiLabel) }})
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

@push('styles')
<style>
.dashboard-modern {
    --line: #e4ebf4;
    --text-main: #1f2a44;
    --text-sub: #5f6f8c;
}

.dashboard-modern .section-title {
    color: var(--text-main);
    letter-spacing: .2px;
}

.dashboard-modern .dashboard-hero {
    border-radius: 16px;
    overflow: hidden;
    background: linear-gradient(120deg, #1d4f91 0%, #1e7b7f 100%);
    position: relative;
}

.dashboard-modern .dashboard-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 85% 15%, rgba(255, 255, 255, .18), transparent 40%);
}

.dashboard-modern .dashboard-hero .card-body {
    position: relative;
    z-index: 1;
}

.dashboard-modern .card {
    border-radius: 14px;
    border: 1px solid var(--line);
}

.dashboard-modern .card-header {
    border-bottom: 1px solid #edf2f8;
    color: var(--text-main);
}

.dashboard-modern .card-header.bg-info,
.dashboard-modern .card-header.bg-warning {
    background: #f6f9fe !important;
    color: #1e3a5f !important;
}

.dashboard-modern input[type="month"].form-control {
    border-color: #d6e0ef;
    border-radius: 10px;
    min-width: 170px;
}

.dashboard-modern .btn-filter {
    border-radius: 10px;
    background: linear-gradient(120deg, #1d4f91 0%, #1e7b7f 100%);
    border: none;
}

.dashboard-modern .btn-reset {
    border-radius: 10px;
    border-color: #d6e0ef !important;
    color: #39537e;
}

.dashboard-modern .small-box {
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 1rem;
    box-shadow: 0 10px 20px rgba(16, 39, 74, .10);
    transition: transform .2s ease, box-shadow .2s ease;
}

.dashboard-modern .small-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 24px rgba(16, 39, 74, .16);
}

.dashboard-modern .small-box .inner {
    padding: 18px;
}

.dashboard-modern .small-box h3 {
    font-weight: 700;
    margin-bottom: 6px;
}

.dashboard-modern .small-box .icon {
    top: 10px;
    right: 12px;
    font-size: 52px;
    opacity: .22;
}

.dashboard-modern .small-box .small-box-footer {
    background: rgba(255, 255, 255, .16);
    color: rgba(255, 255, 255, .96);
    backdrop-filter: blur(1px);
}

.dashboard-modern .small-box .small-box-footer:hover {
    background: rgba(255, 255, 255, .24);
    color: #fff;
}

.dashboard-modern .small-box.bg-info {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
}

.dashboard-modern .small-box.bg-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.dashboard-modern .small-box.bg-success {
    background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
}

.dashboard-modern .small-box.bg-secondary {
    background: linear-gradient(135deg, #64748b 0%, #475569 100%);
}

.dashboard-modern .small-box.bg-primary {
    background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
}

.dashboard-modern .small-box.bg-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.dashboard-modern .small-box.bg-dark {
    background: linear-gradient(135deg, #334155 0%, #1e293b 100%);
}

.dashboard-modern .small-box.bg-teal {
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
}

.dashboard-modern .small-box[class*='bg-'],
.dashboard-modern .small-box[class*='bg-'] p,
.dashboard-modern .small-box[class*='bg-'] h3 {
    color: #fff !important;
}

.dashboard-modern .discipline-card {
    border-radius: 14px;
    border: 1px solid var(--line);
}

.dashboard-modern .discipline-card h3 {
    font-weight: 700;
    color: var(--text-main);
}

.dashboard-modern .card-late {
    background: linear-gradient(145deg, #fff8ef 0%, #ffe9cc 100%);
}

.dashboard-modern .card-late-total {
    background: linear-gradient(145deg, #fff5ec 0%, #ffe1cd 100%);
}

.dashboard-modern .card-ontime {
    background: linear-gradient(145deg, #f2fcf5 0%, #def7e7 100%);
}

.dashboard-modern .card-absent {
    background: linear-gradient(145deg, #fff3f2 0%, #ffdedd 100%);
}

.dashboard-modern .table thead.thead-light th {
    background: #f3f7fd;
    color: #334b73;
    border-bottom: 1px solid #d9e3f1;
}

.dashboard-modern .table tbody tr:hover {
    background: #f9fbff;
}

@media (max-width: 767.98px) {
    .dashboard-modern .dashboard-filter-form {
        width: 100%;
        justify-content: flex-start;
    }
}

:root[data-theme='dark'] .dashboard-modern {
    --line: #2c3a50;
    --text-main: #e2e9f5;
    --text-sub: #9fb0cc;
}

:root[data-theme='dark'] .dashboard-modern .card-header.bg-info,
:root[data-theme='dark'] .dashboard-modern .card-header.bg-warning {
    background: #1b2738 !important;
    color: #dce6f7 !important;
}

:root[data-theme='dark'] .dashboard-modern .table thead.thead-light th {
    background: #1b2738;
    color: #d9e4f6;
    border-bottom-color: #2f3f57;
}

:root[data-theme='dark'] .dashboard-modern .table tbody tr:hover {
    background: #1f2d40;
}

:root[data-theme='dark'] .dashboard-modern .card-late {
    background: linear-gradient(145deg, #3a2f1f 0%, #4a3923 100%);
}

:root[data-theme='dark'] .dashboard-modern .card-late-total {
    background: linear-gradient(145deg, #3d3026 0%, #4c372b 100%);
}

:root[data-theme='dark'] .dashboard-modern .card-ontime {
    background: linear-gradient(145deg, #1f3a2e 0%, #284535 100%);
}

:root[data-theme='dark'] .dashboard-modern .card-absent {
    background: linear-gradient(145deg, #3d2627 0%, #4f3131 100%);
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const disciplineCtx = document.getElementById('disciplineComparisonChart');
const attendanceCtx = document.getElementById('attendanceEventChart');

if (disciplineCtx) {
    new Chart(disciplineCtx, {
        type: 'doughnut',
        data: {
            labels: ['Karyawan Terlambat', 'Tidak Pernah Terlambat'],
            datasets: [{
                data: @json([$karyawanTerlambat, $karyawanTidakPernahTerlambat]),
                backgroundColor: ['#e78a1f', '#2f8f5f'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            cutout: '68%'
        }
    });
}

if (attendanceCtx) {
    new Chart(attendanceCtx, {
        type: 'bar',
        data: {
            labels: ['Hadir', 'Terlambat', 'Tidak Masuk'],
            datasets: [{
                label: 'Jumlah Kejadian',
                data: @json([$totalHadir, $totalKeterlambatan, $totalTidakMasuk]),
                backgroundColor: ['#2f6fb3', '#e78a1f', '#cd5f3d'],
                borderRadius: 10,
                maxBarThickness: 56
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}
</script>
@endpush
