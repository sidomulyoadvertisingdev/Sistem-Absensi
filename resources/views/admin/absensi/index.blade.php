@extends('layouts.app')

@section('title','Daftar Absensi')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Daftar Absensi</h1>

        <div class="d-flex gap-2">
            {{-- INPUT ABSENSI --}}
            <a href="{{ route('admin.absensi.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Input Absensi
            </a>

            {{-- 🔥 DOWNLOAD TEMPLATE CSV --}}
            <a href="{{ route('admin.absensi.template.csv') }}"
               class="btn btn-success">
                <i class="fas fa-file-csv"></i> Template CSV
            </a>

            {{-- 🔥 IMPORT ABSENSI CSV --}}
            <button type="button"
                    class="btn btn-info"
                    data-bs-toggle="modal"
                    data-bs-target="#importAbsensiModal">
                <i class="fas fa-file-upload"></i> Import CSV
            </button>

            {{-- INPUT LEMBUR --}}
            <a href="{{ route('admin.lembur.create') }}" class="btn btn-warning">
                <i class="fas fa-clock"></i> Input Lembur
            </a>
        </div>
    </div>

    {{-- ALERT --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- TABLE --}}
    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Nama</th>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Istirahat</th>
                        <th>Foto</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($data as $absen)
                    <tr>
                        {{-- NAMA --}}
                        <td>{{ $absen->user?->name ?? '-' }}</td>

                        {{-- TANGGAL --}}
                        <td>{{ \Carbon\Carbon::parse($absen->tanggal)->format('d-m-Y') }}</td>

                        {{-- JAM MASUK --}}
                        <td>
                            {{ $absen->jam_masuk
                                ? \Carbon\Carbon::parse($absen->jam_masuk)->format('H:i')
                                : '-' }}
                        </td>

                        {{-- JAM PULANG --}}
                        <td>
                            {{ $absen->jam_pulang
                                ? \Carbon\Carbon::parse($absen->jam_pulang)->format('H:i')
                                : '-' }}
                        </td>

                        {{-- ISTIRAHAT --}}
                        <td>
                            @if($absen->istirahat_mulai || $absen->istirahat_selesai)
                                <div class="small">
                                    <div>
                                        <strong>Mulai:</strong>
                                        <span class="badge badge-warning">
                                            {{ $absen->istirahat_mulai
                                                ? \Carbon\Carbon::parse($absen->istirahat_mulai)->format('H:i')
                                                : '-' }}
                                        </span>
                                    </div>
                                    <div class="mt-1">
                                        <strong>Selesai:</strong>
                                        <span class="badge badge-success">
                                            {{ $absen->istirahat_selesai
                                                ? \Carbon\Carbon::parse($absen->istirahat_selesai)->format('H:i')
                                                : '-' }}
                                        </span>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- FOTO --}}
                        <td class="text-center">
                            @if($absen->foto)
                                <a href="{{ asset('storage/'.$absen->foto) }}" target="_blank">
                                    <img src="{{ asset('storage/'.$absen->foto) }}"
                                         width="60"
                                         class="img-thumbnail">
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- STATUS --}}
                        <td>
                            @switch($absen->status)
                                @case('hadir')
                                    <span class="badge badge-success">Hadir</span>
                                    @break
                                @case('terlambat')
                                    <span class="badge badge-warning">Terlambat</span>
                                    @break
                                @case('izin')
                                    <span class="badge badge-info">Izin</span>
                                    @break
                                @case('sakit')
                                    <span class="badge badge-primary">Sakit</span>
                                    @break
                                @default
                                    <span class="badge badge-secondary">
                                        {{ ucfirst($absen->status) }}
                                    </span>
                            @endswitch
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            Belum ada data absensi
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ================= MODAL IMPORT CSV ================= --}}
<div class="modal fade" id="importAbsensiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST"
              action="{{ route('admin.absensi.import.csv') }}"
              enctype="multipart/form-data"
              class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">Import Absensi (CSV)</h5>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="file"
                       name="file"
                       class="form-control"
                       accept=".csv"
                       required>

                <div class="alert alert-info mt-3">
                    <strong>Format CSV:</strong><br>
                    <code>tanggal,nama,jam_masuk,istirahat_mulai,istirahat_selesai,jam_pulang</code>
                    <br><br>
                    <strong>Contoh:</strong><br>
                    <code>2026-01-27,Albiatun,08:10,12:00,13:00,17:00</code>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-primary">
                    <i class="fas fa-upload"></i> Import
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
