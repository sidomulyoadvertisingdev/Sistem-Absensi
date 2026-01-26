@extends('layouts.app')

@section('title','Gaji Karyawan')

@section('content')
<div class="container-fluid">

    <h4 class="mb-4 font-weight-bold">Pengaturan Gaji Per Karyawan</h4>

    {{-- FILTER BULAN --}}
    <form method="GET" class="mb-3">
        <div class="form-row align-items-end">
            <div class="col-md-3">
                <label>Periode</label>
                <input type="month"
                       name="bulan"
                       value="{{ $bulan }}"
                       class="form-control">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary">
                    <i class="fas fa-search"></i> Tampilkan
                </button>
            </div>
        </div>
    </form>

    {{-- INFO BULAN --}}
    <div class="mb-3">
        <strong>Periode:</strong>
        {{ \Carbon\Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y') }}
    </div>

    {{-- ALERT --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-hover table-sm mb-0">
                <thead class="thead-light text-center">
                    <tr>
                        <th>Nama</th>
                        <th>Gaji Pokok</th>
                        <th>Tunj. Umum</th>
                        <th>Tunj. Transport</th>
                        <th>Tunj. THR</th>
                        <th>Tunj. Kesehatan</th>
                        <th>Status</th>
                        <th width="200">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                    @php
                        $salary = $user->salary;
                    @endphp
                    <tr>
                        <td>{{ $user->name }}</td>

                        <td class="text-right">
                            Rp {{ number_format($salary->gaji_pokok ?? 0, 0, ',', '.') }}
                        </td>

                        <td class="text-right">
                            Rp {{ number_format($salary->tunjangan_umum ?? 0, 0, ',', '.') }}
                        </td>

                        <td class="text-right">
                            Rp {{ number_format($salary->tunjangan_transport ?? 0, 0, ',', '.') }}
                        </td>

                        <td class="text-right">
                            Rp {{ number_format($salary->tunjangan_thr ?? 0, 0, ',', '.') }}
                        </td>

                        <td class="text-right">
                            Rp {{ number_format($salary->tunjangan_kesehatan ?? 0, 0, ',', '.') }}
                        </td>

                        {{-- STATUS --}}
                        <td class="text-center">
                            @if($salary && $salary->aktif)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-secondary">Belum Diatur</span>
                            @endif
                        </td>

                        {{-- AKSI --}}
                        <td class="text-center">
                            {{-- ATUR GAJI --}}
                            <a href="{{ route('admin.gaji.edit', $user->id) }}"
                               class="btn btn-sm btn-primary mb-1">
                                <i class="fas fa-cog"></i> Atur
                            </a>

                            {{-- SLIP GAJI --}}
                            @if($salary && $salary->aktif)
                                <a href="{{ route('admin.gaji.slip.pdf', [$user->id, 'bulan' => $bulan]) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-danger mb-1">
                                    <i class="fas fa-file-pdf"></i> Slip
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">
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
