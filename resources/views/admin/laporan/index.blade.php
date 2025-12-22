@extends('layouts.app')

@section('title','Laporan Bulanan')

@section('content')
<div class="container-fluid">

    <h1 class="mb-4">Laporan Bulanan Karyawan</h1>

    {{-- FILTER --}}
    <form method="GET" class="row mb-4">
        <div class="col-md-3">
            <label>Bulan</label>
            <select name="bulan" class="form-control">
                @for($i=1;$i<=12;$i++)
                    <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                        {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                    </option>
                @endfor
            </select>
        </div>

        <div class="col-md-3">
            <label>Tahun</label>
            <select name="tahun" class="form-control">
                @for($y=date('Y')-3;$y<=date('Y');$y++)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>
        </div>

        <div class="col-md-3 align-self-end">
            <button class="btn btn-primary">
                <i class="fas fa-search"></i> Tampilkan
            </button>
        </div>
    </form>

    {{-- TABLE --}}
    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>Nama</th>
                        <th>Hari Kerja</th>
                        <th>Hadir</th>
                        <th>Izin</th>
                        <th>Sakit</th>
                        <th>Lembur</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($laporan as $row)
                        <tr>
                            <td>{{ $row['nama'] }}</td>
                            <td>{{ $row['hari_kerja'] }}</td>
                            <td>{{ $row['hadir'] }}</td>
                            <td>{{ $row['izin'] }}</td>
                            <td>{{ $row['sakit'] }}</td>
                            <td>
                                {{ $row['lembur_jam'] }} jam
                                {{ $row['lembur_menit'] }} menit
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                Tidak ada data
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
