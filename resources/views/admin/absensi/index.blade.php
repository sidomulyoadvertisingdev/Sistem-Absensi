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
                        <td>
                            {{ \Carbon\Carbon::parse($absen->tanggal)->format('d-m-Y') }}
                        </td>

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
                                    <img
                                        src="{{ asset('storage/'.$absen->foto) }}"
                                        alt="Foto Absen"
                                        width="60"
                                        class="img-thumbnail"
                                    >
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
@endsection
