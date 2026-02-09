<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Laporan Gaji Bulanan</title>

<style>
@page { size:A4 landscape; margin:10mm; }

body{
font-family:DejaVu Sans,sans-serif;
font-size:8px;
color:#000;
}

/* ================= HEADER ================= */

.header{ margin-bottom:6px; }

.header-table{ width:100%; border:none; }
.header-table td{ border:none; vertical-align:middle; }

.logo{ width:120px; }

h2{
margin:0;
font-size:12px;
text-align:center;
}

/* ================= TABLE ================= */

table{
width:100%;
border-collapse:collapse;
table-layout:fixed;
}

th,td{
border:1px solid #333;
padding:3px;
vertical-align:middle;
white-space:nowrap;
line-height:1.2;
}

th{
background:#eaeaea;
font-weight:bold;
text-align:center;
}

/* kolom toko aman */

td.toko{
white-space:nowrap;
overflow:hidden;
text-overflow:ellipsis;
max-width:80px;
}

/* kolom nama 1 baris */

td.nama{
white-space:nowrap;
overflow:hidden;
text-overflow:ellipsis;
max-width:140px;
}

/* angka kanan */

td.text-right{
text-align:right;
}

tr{ page-break-inside:avoid; }

/* ================= SUMMARY ================= */

.summary{
margin-top:8px;
width:45%;
float:right;
}

.summary td{ font-weight:bold; }

/* ================= FOOTER ================= */

.footer{
clear:both;
margin-top:6px;
font-size:8px;
text-align:right;
}
</style>
</head>

<body>

@php
$periode = \Carbon\Carbon::create($tahun,$bulan)->translatedFormat('F Y');
$totalDiterima = collect($laporan)->sum('gaji_diterima');
$hariKerja = 26;
$logo = public_path('logo-perusahaan.png');
@endphp

{{-- ================= HEADER ================= --}}

<div class="header">
<table class="header-table">
<tr>

<td width="140">
@if(file_exists($logo))
<img src="{{ $logo }}" class="logo">
@endif
</td>

<td>
<h2>
LAPORAN GAJI KARYAWAN<br>
BULAN {{ $periode }}
</h2>
</td>

<td width="140"></td>

</tr>
</table>
</div>

{{-- ================= TABLE ================= --}}

<table>

<thead>
<tr>

<th width="3%">No</th>
<th width="6%">Toko</th>
<th width="12%">Nama</th>

<th>HK</th>
<th>HDR</th>
<th>TMB</th>
<th>TLT</th>
<th>MNT</th>
<th>OFF</th>

<th>/HR</th>
<th>G.NRM</th>
<th>B.HR</th>

<th>TUNJ</th>

<th>LBR</th>
<th>B.JOB</th>

<th>P.TRN</th>
<th>POT</th>
<th>KOTOR</th>
<th>TERIMA</th>

</tr>
</thead>

<tbody>

@forelse($laporan as $row)

@php
$totalTunjangan =
($row['tunjangan_umum'] ?? 0) +
($row['tunjangan_transport'] ?? 0) +
($row['tunjangan_thr'] ?? 0) +
($row['tunjangan_kesehatan'] ?? 0);
@endphp

<tr>

<td>{{ $row['no'] ?? 0 }}</td>

<td class="toko">
{{ $row['toko'] ?? '-' }}
</td>

<td class="nama">
{{ $row['nama'] ?? '-' }}
</td>

<td>{{ $hariKerja }}</td>

<td>{{ $row['hari_hadir'] ?? 0 }}</td>
<td>{{ $row['hari_tambahan'] ?? 0 }}</td>
<td>{{ $row['hari_telat'] ?? 0 }}</td>
<td>{{ $row['menit_telat'] ?? 0 }}</td>
<td>{{ $row['hari_tidak_masuk'] ?? 0 }}</td>

<td class="text-right">
{{ number_format($row['gaji_per_hari'] ?? 0,0,',','.') }}
</td>

<td class="text-right">
{{ number_format($row['gaji_bruto'] ?? 0,0,',','.') }}
</td>

<td class="text-right">
{{ number_format($row['gaji_bonus'] ?? 0,0,',','.') }}
</td>

<td class="text-right">
{{ number_format($totalTunjangan,0,',','.') }}
</td>

<td class="text-right">
{{ number_format($row['lembur'] ?? 0,0,',','.') }}
</td>

<td class="text-right">
{{ number_format($row['bonus_job'] ?? 0,0,',','.') }}
</td>

<td class="text-right">
{{ number_format($row['potongan_training'] ?? 0,0,',','.') }}
</td>

<td class="text-right">
{{ number_format($row['total_potongan'] ?? 0,0,',','.') }}
</td>

<td class="text-right">
{{ number_format($row['salary_kotor'] ?? 0,0,',','.') }}
</td>

<td class="text-right">
<strong>
{{ number_format($row['gaji_diterima'] ?? 0,0,',','.') }}
</strong>
</td>

</tr>

@empty
<tr>
<td colspan="19" style="text-align:center;">
Tidak ada data laporan
</td>
</tr>
@endforelse

</tbody>

</table>

{{-- ================= SUMMARY ================= --}}

<table class="summary">
<tr>
<td>Total Gaji</td>
<td class="text-right">
<strong>
Rp {{ number_format($totalDiterima,0,',','.') }}
</strong>
</td>
</tr>
</table>

<div class="footer">
Dicetak {{ now()->translatedFormat('d F Y H:i') }}
</div>

</body>
</html>
