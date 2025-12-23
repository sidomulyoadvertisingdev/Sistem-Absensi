@extends('layouts.app')

@section('title', 'Jadwal Kerja Karyawan')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Jadwal Kerja Karyawan</h1>
    </div>

    {{-- ALERT --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card">
        <div class="card-body table-responsive p-0">

            <table class="table table-bordered table-hover mb-0">
                <thead class="thead-light text-center">
                    <tr>
                        <th rowspan="2">Nama</th>
                        <th colspan="7">Hari Kerja</th>
                        <th rowspan="2" width="120">Aksi</th>
                    </tr>
                    <tr>
                        <th>Senin</th>
                        <th>Selasa</th>
                        <th>Rabu</th>
                        <th>Kamis</th>
                        <th>Jumat</th>
                        <th>Sabtu</th>
                        <th>Minggu</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>

                        @php
                            $hariList = [
                                'senin','selasa','rabu',
                                'kamis','jumat','sabtu','minggu'
                            ];
                        @endphp

                        @foreach($hariList as $hari)
                            @php
                                $jadwal = $user->workSchedules
                                    ->where('hari', $hari)
                                    ->first();
                            @endphp

                            <td class="text-center">
                                @if($jadwal && $jadwal->aktif)
                                    <span class="badge badge-success">
                                        {{ $jadwal->jam_masuk }} - {{ $jadwal->jam_pulang }}
                                    </span>
                                @elseif($jadwal && !$jadwal->aktif)
                                    <span class="badge badge-secondary">
                                        Libur
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        @endforeach

                        <td class="text-center">
                            <a href="{{ route('admin.jadwal.edit', $user->id) }}"
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-clock"></i> Atur
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">
                            Tidak ada data karyawan
                        </td>
                    </tr>
                @endforelse
                </tbody>

            </table>

        </div>
    </div>

</div>
@endsection
