@extends('layouts.app')

@section('title','Laporan Gaji Bulanan')

@section('content')
<div class="container-fluid">

    {{-- ================= STYLE ================= --}}
    <style>
        /* Paksa semua kolom 1 baris */
        .table-nowrap th,
        .table-nowrap td {
            white-space: nowrap;
            vertical-align: middle;
        }

        /* Batasi kolom nama karyawan */
        .col-nama {
            max-width: 220px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>

    <h4 class="mb-4 text-center font-weight-bold">
        LAPORAN GAJI KARYAWAN BULAN
        {{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }}
    </h4>

    {{-- ================= FILTER & ACTION ================= --}}
    <form method="GET" class="row mb-3 align-items-end">
        <div class="col-md-3">
            <label>Bulan</label>
            <select name="bulan" class="form-control">
                @for($i=1;$i<=12;$i++)
                    <option value="{{ $i }}" {{ (int)$bulan === $i ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>
        </div>

        <div class="col-md-3">
            <label>Tahun</label>
            <select name="tahun" class="form-control">
                @for($y=date('Y')-3;$y<=date('Y');$y++)
                    <option value="{{ $y }}" {{ (int)$tahun === $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>
        </div>

        <div class="col-md-6 text-right">
            <button class="btn btn-primary mr-2">
                <i class="fas fa-search"></i> Tampilkan
            </button>

            {{-- 🔥 EXPORT PDF --}}
            <a href="{{ route('admin.laporan.gaji.pdf', [
                'bulan' => $bulan,
                'tahun' => $tahun
            ]) }}"
               target="_blank"
               class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
        </div>
    </form>

    {{-- ================= TABLE ================= --}}
    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-sm text-center table-nowrap">
                <thead class="thead-dark">
                    <tr>
                        <th>No</th>
                        <th>Toko</th>
                        <th>Karyawan</th>
                        <th>Jumlah Hari</th>
                        <th>Off Day</th>
                        <th>Presensi</th>
                        <th>Gaji Pokok</th>
                        <th>Tunj. Umum</th>
                        <th>Tunj. Transport</th>
                        <th>THR</th>
                        <th>Kesehatan</th>
                        <th>Per Hari</th>
                        <th>Telat (Hari)</th>
                        <th>Lembur / Poin</th>
                        <th>Menit Telat</th>
                        <th>Pot. Telat</th>
                        <th>Salary</th>
                        <th>Total Gaji</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($laporan as $row)
                        <tr>
                            <td>{{ $row['no'] }}</td>
                            <td>{{ $row['toko'] }}</td>

                            {{-- NAMA (1 BARIS + TOOLTIP) --}}
                            <td class="text-left col-nama" title="{{ $row['nama'] }}">
                                {{ $row['nama'] }}
                            </td>

                            <td>{{ $row['jumlah_hari'] }}</td>
                            <td>{{ $row['off_day'] }}</td>
                            <td>{{ $row['presensi_masuk'] }}</td>

                            <td class="text-right">
                                {{ number_format($row['gaji_pokok'],0,',','.') }}
                            </td>

                            <td class="text-right">
                                {{ number_format($row['tunjangan_umum'],0,',','.') }}
                            </td>

                            <td class="text-right">
                                {{ number_format($row['tunjangan_transport'],0,',','.') }}
                            </td>

                            <td class="text-right">
                                {{ number_format($row['tunjangan_hari_raya'],0,',','.') }}
                            </td>

                            <td class="text-right">
                                {{ number_format($row['tunjangan_kesehatan'],0,',','.') }}
                            </td>

                            <td class="text-right">
                                {{ number_format($row['hitungan_per_hari'],0,',','.') }}
                            </td>

                            <td>
                                @if($row['kerja_tidak_jam'] > 0)
                                    <span class="badge badge-warning">
                                        {{ $row['kerja_tidak_jam'] }}
                                    </span>
                                @else
                                    0
                                @endif
                            </td>

                            <td class="text-right">
                                {{ number_format($row['lembur_poin_lain'],0,',','.') }}
                            </td>

                            <td>
                                @if($row['potongan_n_telat'] > 0)
                                    <span class="text-danger">
                                        {{ $row['potongan_n_telat'] }}
                                    </span>
                                @else
                                    0
                                @endif
                            </td>

                            <td class="text-right text-danger">
                                {{ number_format($row['nominal_potongan_telat'],0,',','.') }}
                            </td>

                            <td class="text-right">
                                {{ number_format($row['salary'],0,',','.') }}
                            </td>

                            <td class="text-right font-weight-bold text-success">
                                {{ number_format($row['total_gaji'],0,',','.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="18" class="text-center text-muted">
                                Tidak ada data laporan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
