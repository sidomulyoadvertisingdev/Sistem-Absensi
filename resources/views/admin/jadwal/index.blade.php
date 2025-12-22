@extends('layouts.app')

@section('title', 'Jadwal Kerja User')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Jadwal Kerja Per User</h1>
    </div>

    {{-- Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Nama</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Istirahat</th>
                        <th>Status</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                    @php
                        $schedule = $user->workSchedule;
                    @endphp
                    <tr>
                        <td>{{ $user->name }}</td>

                        <td>
                            {{ $schedule->jam_masuk ?? '-' }}
                        </td>

                        <td>
                            {{ $schedule->jam_pulang ?? '-' }}
                        </td>

                        <td>
                            @if($schedule && $schedule->istirahat_mulai && $schedule->istirahat_selesai)
                                <div class="small">
                                    <div>
                                        <strong>Mulai</strong> :
                                        <span class="badge badge-warning">
                                            {{ $schedule->istirahat_mulai }}
                                        </span>
                                    </div>
                                    <div class="mt-1">
                                        <strong>Selesai</strong> :
                                        <span class="badge badge-success">
                                            {{ $schedule->istirahat_selesai }}
                                        </span>
                                    </div>
                                </div>
                            @elseif($schedule && ($schedule->istirahat_mulai || $schedule->istirahat_selesai))
                                <span class="text-warning">
                                    Istirahat belum lengkap
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td>
                            @if($schedule && $schedule->aktif)
                                <span class="badge badge-success">Aktif</span>
                            @elseif($schedule)
                                <span class="badge badge-secondary">Nonaktif</span>
                            @else
                                <span class="badge badge-warning">Belum Diatur</span>
                            @endif
                        </td>

                        <td>
                            <a href="{{ route('admin.jadwal.edit', $user->id) }}"
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-clock"></i> Atur
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            Tidak ada data user
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
