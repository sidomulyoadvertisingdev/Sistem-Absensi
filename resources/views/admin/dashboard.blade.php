@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">

    {{-- PAGE TITLE --}}
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="m-0">Dashboard</h1>
            <small class="text-muted">
                Selamat datang, {{ auth()->user()->name }}
            </small>
        </div>
    </div>

    {{-- INFO BOX UTAMA --}}
    <div class="row">

        {{-- TOTAL ABSENSI --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalAbsensi }}</h3>
                    <p>Total Absensi</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <a href="{{ route('admin.absensi') }}" class="small-box-footer">
                    Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        {{-- TOTAL LEMBUR --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $totalLembur }}</h3>
                    <p>Total Lembur</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <a href="{{ route('admin.lembur') }}" class="small-box-footer">
                    Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        {{-- DATA GAJI --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $totalGaji }}</h3>
                    <p>Data Gaji</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <a href="{{ route('admin.gaji') }}" class="small-box-footer">
                    Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        {{-- TOTAL KARYAWAN --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $totalUser }}</h3>
                    <p>Total Karyawan</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('admin.karyawan.index') }}" class="small-box-footer">
                    Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

    </div>

    {{-- INFO BOX PENEMPATAN --}}
    <div class="row mt-4">
        <div class="col-lg-4 col-12">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $karyawanSMLeccy }}</h3>
                    <p>Karyawan SM Lecy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-industry"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-12">
            <div class="small-box bg-dark">
                <div class="inner">
                    <h3>{{ $karyawanGudang }}</h3>
                    <p>Karyawan SM Gudang</p>
                </div>
                <div class="icon">
                    <i class="fas fa-warehouse"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-12">
            <div class="small-box bg-teal">
                <div class="inner">
                    <h3>{{ $karyawanSMPenempatan }}</h3>
                    <p>Karyawan SM Percetakan</p>
                </div>
                <div class="icon">
                    <i class="fas fa-print"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- DATA TERBARU --}}
    <div class="row mt-4">

        {{-- ABSENSI TERBARU --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Absensi Terbaru</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Jam</th>
                                <th>Aksi</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($absensiTerbaru as $item)
                                <tr>
                                    <td>{{ $item->user->name ?? '-' }}</td>
                                    <td>{{ $item->jam_tampil ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $item->aksi_badge }}">
                                            {{ $item->aksi_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $item->status_badge }}">
                                            {{ $item->status_label }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        Belum ada data absensi
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- LEMBUR TERBARU --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Lembur Terbaru</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lemburTerbaru as $item)
                                <tr>
                                    <td>{{ $item->user->name ?? '-' }}</td>
                                    <td>{{ $item->tanggal }}</td>
                                    <td>
                                        <span class="badge badge-warning">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">
                                        Belum ada data lembur
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
