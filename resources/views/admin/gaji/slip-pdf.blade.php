<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Slip Gaji</title>

<style>
body{font-family:DejaVu Sans;font-size:11px;color:#222}

.header{
background:#1f4fa3;
color:#fff;
padding:12px 16px;
}

.header-table{
width:100%;
}

.logo{
width:70px;
}

.header-title{
text-align:center;
font-size:16px;
font-weight:bold;
}

.container{padding:22px}

table{width:100%;border-collapse:collapse}

.info th,.info td{padding:4px 6px}

.section-title{
background:#1f4fa3;
color:#fff;
padding:6px;
font-weight:bold;
margin-top:12px;
}

.box td,.box th{
padding:6px;
border-bottom:1px solid #eee;
}

.right{text-align:right}
.center{text-align:center}

.net{
margin-top:16px;
padding:12px;
border:2px solid #1f4fa3;
font-size:14px;
font-weight:bold;
text-align:center;
}

.note{font-size:9px;color:#666}
</style>

</head>

<body>

@php
$logo = public_path('logo-perusahaan.png');

/* SAFE VARIABLES */
$periode = $periode ?? now()->translatedFormat('F Y');

$hariHadir = $hariHadir ?? 0;
$hariTelat = $hariTelat ?? 0;
$menitTerlambat = $menitTerlambat ?? 0;

$gajiPerHari = $gajiPerHari ?? 0;
$gajiDasar = $gajiDasar ?? 0;

$totalJamLembur = $totalJamLembur ?? 0;
$totalLembur = $totalLembur ?? 0;

$totalBonusJob = $totalBonusJob ?? 0;

$salaryKotor = $salaryKotor ?? 0;
$totalPotongan = $totalPotongan ?? 0;
$totalGaji = $totalGaji ?? 0;

$potonganTelatNominal = $potonganTelatNominal ?? 0;

$tunjUmum = $salary->tunjangan_umum ?? 0;
$tunjTransport = $salary->tunjangan_transport ?? 0;
$tunjThr = $salary->tunjangan_thr ?? 0;
$tunjSehat = $salary->tunjangan_kesehatan ?? 0;

$jobBonus = $jobBonus ?? collect();
@endphp


{{-- ================= HEADER DENGAN LOGO ================= --}}

<div class="header">

<table class="header-table">

<tr>

<td width="80">
@if(file_exists($logo))
<img src="{{ $logo }}" class="logo">
@endif
</td>

<td class="header-title">
CV. Sidomulyo Advertising<br>
SLIP GAJI — {{ $periode }}
</td>

<td width="80"></td>

</tr>

</table>

</div>


<div class="container">

{{-- ================= INFO ================= --}}

<table class="info">

<tr>
<th>Nama</th>
<td>: {{ $user->name }}</td>

<th>Hadir</th>
<td>: {{ $hariHadir }}</td>
</tr>

<tr>
<th>Penempatan</th>
<td>: {{ $user->penempatan ?? '-' }}</td>

<th>Terlambat</th>
<td>: {{ $hariTelat }}x ({{ $menitTerlambat }} menit)</td>
</tr>

</table>


{{-- ================= PENERIMAAN ================= --}}

<div class="section-title">PENERIMAAN</div>

<div class="box">

<table>

<tr>
<td>Gaji Harian</td>
<td class="right">Rp {{ number_format($gajiPerHari,0,',','.') }}</td>
</tr>

<tr>
<td>Gaji Presensi</td>
<td class="right">Rp {{ number_format($gajiDasar,0,',','.') }}</td>
</tr>

<tr>
<td>Tunjangan Umum</td>
<td class="right">Rp {{ number_format($tunjUmum,0,',','.') }}</td>
</tr>

<tr>
<td>Tunjangan Transport</td>
<td class="right">Rp {{ number_format($tunjTransport,0,',','.') }}</td>
</tr>

<tr>
<td>Tunjangan THR</td>
<td class="right">Rp {{ number_format($tunjThr,0,',','.') }}</td>
</tr>

<tr>
<td>Tunjangan Kesehatan</td>
<td class="right">Rp {{ number_format($tunjSehat,0,',','.') }}</td>
</tr>

<tr>
<td>Lembur</td>
<td class="right">Rp {{ number_format($totalLembur,0,',','.') }}</td>
</tr>

<tr>
<td>Bonus Job</td>
<td class="right">Rp {{ number_format($totalBonusJob,0,',','.') }}</td>
</tr>

<tr>
<th>Salary Kotor</th>
<th class="right">Rp {{ number_format($salaryKotor,0,',','.') }}</th>
</tr>

</table>

@if(empty($salary->include_tunjangan))
<div class="note">
* Tunjangan hanya ditampilkan — tidak dihitung dalam gaji diterima.
</div>
@endif

</div>


{{-- ================= POTONGAN ================= --}}

<div class="section-title">POTONGAN</div>

<div class="box">

<table>

<tr>
<td>Potongan Terlambat</td>
<td class="right">Rp {{ number_format($potonganTelatNominal,0,',','.') }}</td>
</tr>

<tr>
<th>Total Potongan</th>
<th class="right">Rp {{ number_format($totalPotongan,0,',','.') }}</th>
</tr>

</table>

</div>


{{-- ================= TOTAL ================= --}}

<div class="net">
TOTAL GAJI DITERIMA<br>
Rp {{ number_format($totalGaji,0,',','.') }}
</div>


{{-- ================= BONUS DETAIL ================= --}}

<div class="section-title">DETAIL BONUS</div>

<div class="box">

<table>

<tr>
<th>Job</th>
<th class="right">Bonus</th>
</tr>

@forelse($jobBonus as $job)
<tr>
<td>{{ $job->title }}</td>
<td class="right">Rp {{ number_format($job->bonus,0,',','.') }}</td>
</tr>
@empty
<tr>
<td colspan="2" class="center">Tidak ada bonus</td>
</tr>
@endforelse

</table>

</div>

</div>

</body>
</html>
