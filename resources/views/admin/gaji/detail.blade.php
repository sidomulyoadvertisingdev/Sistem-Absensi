@extends('layouts.app')

@section('title', 'Detail Gaji Karyawan')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            Detail Gaji - {{ $user->name }}
            <small class="text-muted">({{ $periode }})</small>
        </h4>

        <div class="d-flex align-items-center gap-2">

            {{-- EXPORT SLIP --}}
            <a href="{{ route('admin.gaji.slip.pdf', [$user->id, 'bulan' => $bulan]) }}"
               target="_blank"
               class="btn btn-outline-secondary">
                <i class="fas fa-file-pdf"></i> Export Slip
            </a>

            {{-- TOMBOL BAYAR / STATUS --}}
            @if(!$salary->is_paid)
                <form method="POST"
                      action="{{ route('admin.gaji.pay', [$user->id, 'bulan' => $bulan]) }}"
                      onsubmit="return confirm('Bayar gaji dan kunci absensi?')">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-money-bill-wave"></i> Bayar Gaji
                    </button>
                </form>
            @else
                <span class="badge badge-success p-2">
                    <i class="fas fa-check-circle"></i> Sudah Dibayar
                </span>
            @endif

        </div>
    </div>

    {{-- INFO PEMBAYARAN --}}
    @if($salary->is_paid)
        <div class="alert alert-success">
            <strong>Informasi Pembayaran</strong>
            <ul class="mb-0">
                <li>
                    <strong>Tanggal Bayar:</strong>
                    {{ optional($salary->paid_at)->format('d M Y H:i') }}
                </li>
                <li>
                    <strong>Dibayar Oleh:</strong>
                    {{ optional($salary->payer)->name ?? 'Admin' }}
                </li>
            </ul>
        </div>
    @endif

    {{-- RINGKASAN --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <small>Hari Hadir</small>
                    <h4 class="mb-0">{{ $hariHadir }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <small>Hari Terlambat</small>
                    <h4 class="mb-0">{{ $hariTelat }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <small>Menit Terlambat</small>
                    <h4 class="mb-0">{{ $menitTerlambat }} menit</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <small>Total Diterima</small>
                    <h4 class="mb-0">
                        Rp {{ number_format($totalGaji, 0, ',', '.') }}
                    </h4>
                </div>
            </div>
        </div>
    </div>

    {{-- ABSENSI --}}
    <div class="card mb-4">
        <div class="card-header">
            <strong>Detail Absensi</strong>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-sm mb-0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Masuk</th>
                        <th>Pulang</th>
                        <th>Status</th>
                        <th>Menit Terlambat</th>
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
                        <td>{{ $a->menit_terlambat }} menit</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            Tidak ada absensi
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- BONUS JOB --}}
    <div class="card mb-4">
        <div class="card-header">
            <strong>Bonus Job</strong>
        </div>
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
                        <td>
                            Rp {{ number_format($job->bonus, 0, ',', '.') }}
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
        </div>
    </div>

    {{-- RINGKASAN GAJI --}}
    <div class="card">
        <div class="card-header">
            <strong>Ringkasan Gaji</strong>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <tr>
                    <th>Gaji Kotor</th>
                    <td>
                        Rp {{ number_format($salaryKotor, 0, ',', '.') }}
                    </td>
                </tr>
                <tr>
                    <th>Total Potongan</th>
                    <td class="text-danger">
                        Rp {{ number_format($totalPotongan, 0, ',', '.') }}
                    </td>
                </tr>
                <tr>
                    <th>Total Diterima</th>
                    <td class="font-weight-bold text-success">
                        Rp {{ number_format($totalGaji, 0, ',', '.') }}
                    </td>
                </tr>
            </table>
        </div>
    </div>

</div>
@endsection
