@extends('layouts.app')

@section('title','Laporan Gaji Bulanan')

@section('content')
<div class="container-fluid">

<style>
.table-nowrap th,
.table-nowrap td {
    white-space: nowrap;
    vertical-align: middle;
}
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

{{-- FILTER --}}
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

<a href="{{ route('admin.laporan.gaji.pdf',['bulan'=>$bulan,'tahun'=>$tahun]) }}"
target="_blank"
class="btn btn-danger">
<i class="fas fa-file-pdf"></i> Export PDF
</a>
</div>

</form>

{{-- TABLE --}}
<div class="card">
<div class="card-body table-responsive p-0">

<table class="table table-bordered table-sm text-center table-nowrap">

<thead class="thead-dark">
<tr>

<th>No</th>
<th>Toko</th>
<th>Karyawan</th>

<th>Hadir</th>
<th>Normal</th>
<th>Tambahan</th>
<th>Telat</th>
<th>Menit Telat</th>
<th>Tidak Masuk</th>

<th>Gaji/Hari</th>
<th>Gaji Normal</th>
<th>Bonus Hari</th>

<th>Umum</th>
<th>Transport</th>
<th>THR</th>
<th>Kesehatan</th>

<th>Lembur</th>
<th>Bonus Job</th>

<th>Potongan</th>
<th>Salary Kotor</th>
<th>Gaji Diterima</th>

</tr>
</thead>

<tbody>

@forelse($laporan as $row)

<tr>

<td>{{ $row['no'] ?? 0 }}</td>
<td>{{ $row['toko'] ?? '-' }}</td>

<td class="text-left col-nama">
{{ $row['nama'] ?? '-' }}
</td>

<td>{{ $row['hari_hadir'] ?? 0 }}</td>
<td>{{ $row['hari_normal'] ?? 0 }}</td>

<td>
@if(($row['hari_tambahan'] ?? 0) > 0)
<span class="badge badge-success">
+{{ $row['hari_tambahan'] }}
</span>
@else 0 @endif
</td>

<td>
@if(($row['hari_telat'] ?? 0) > 0)
<span class="badge badge-danger">
{{ $row['hari_telat'] }}
</span>
@else 0 @endif
</td>

<td>
@if(($row['menit_telat'] ?? 0) > 0)
<span class="badge badge-warning">
{{ $row['menit_telat'] }} menit
</span>
@else 0 @endif
</td>

<td>
@if(($row['hari_tidak_masuk'] ?? 0) > 0)
<span class="badge badge-secondary">
{{ $row['hari_tidak_masuk'] }}
</span>
@else 0 @endif
</td>

<td class="text-right">
{{ number_format($row['gaji_per_hari'] ?? 0,0,',','.') }}
</td>

<td class="text-right font-weight-bold">
{{ number_format($row['gaji_bruto'] ?? 0,0,',','.') }}
</td>

<td class="text-right text-primary">
{{ number_format($row['gaji_bonus'] ?? 0,0,',','.') }}
</td>

<td class="text-right">
{{ number_format($row['tunjangan_umum'] ?? 0,0,',','.') }}
</td>

<td class="text-right">
{{ number_format($row['tunjangan_transport'] ?? 0,0,',','.') }}
</td>

<td class="text-right">
{{ number_format($row['tunjangan_thr'] ?? 0,0,',','.') }}
</td>

<td class="text-right">
{{ number_format($row['tunjangan_kesehatan'] ?? 0,0,',','.') }}
</td>

<td class="text-right">
{{ number_format($row['lembur'] ?? 0,0,',','.') }}
</td>

<td class="text-right text-info">
{{ number_format($row['bonus_job'] ?? 0,0,',','.') }}
</td>

<td class="text-right text-danger">
{{ number_format($row['total_potongan'] ?? 0,0,',','.') }}
</td>

<td class="text-right">
{{ number_format($row['salary_kotor'] ?? 0,0,',','.') }}
</td>

<td class="text-right font-weight-bold text-success">
{{ number_format($row['gaji_diterima'] ?? 0,0,',','.') }}
</td>

</tr>

@empty
<tr>
<td colspan="21" class="text-center text-muted">
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
