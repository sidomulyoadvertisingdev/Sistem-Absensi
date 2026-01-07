@extends('layouts.app')

@section('title','Gaji User')

@section('content')
<div class="container-fluid">

    <h1 class="mb-4">Pengaturan Gaji Per Karyawan</h1>

    {{-- INFO BULAN --}}
    <div class="mb-3">
        <strong>Periode:</strong> {{ \Carbon\Carbon::parse($bulan)->translatedFormat('F Y') }}
    </div>

    {{-- ALERT --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Nama</th>
                        <th>Gaji Pokok</th>
                        <th>Uang Makan</th>
                        <th>Transport</th>
                        <th>Status</th>
                        <th width="180">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>

                        <td>
                            Rp {{ number_format($user->salary->gaji_pokok ?? 0, 0, ',', '.') }}
                        </td>

                        <td>
                            Rp {{ number_format($user->salary->uang_makan ?? 0, 0, ',', '.') }}
                        </td>

                        <td>
                            Rp {{ number_format($user->salary->transport ?? 0, 0, ',', '.') }}
                        </td>

                        <td>
                            @if($user->salary && $user->salary->aktif)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-secondary">Belum Diatur</span>
                            @endif
                        </td>

                        <td>
                            {{-- ATUR GAJI --}}
                            <a href="{{ route('admin.gaji.edit', $user->id) }}"
                               class="btn btn-sm btn-primary mb-1">
                                <i class="fas fa-cog"></i> Atur
                            </a>

                            {{-- SLIP GAJI --}}
                            @if($user->salary && $user->salary->aktif)
                                <a href="{{ route('admin.gaji.slip.pdf', $user->id) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-danger mb-1">
                                    <i class="fas fa-file-pdf"></i> Slip
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
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
