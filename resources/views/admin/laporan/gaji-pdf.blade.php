<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Gaji Bulanan</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            color: #000;
        }

        h2 {
            text-align: center;
            margin-bottom: 8px;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* 🔥 PENTING */
        }

        th, td {
            border: 1px solid #333;
            padding: 3px 4px;
            white-space: nowrap;
            vertical-align: middle;
        }

        th {
            background: #eaeaea;
            font-weight: bold;
            text-align: center;
        }

        td.text-left {
            text-align: left;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        td.text-right {
            text-align: right;
        }

        tr {
            page-break-inside: avoid;
        }

        .footer {
            margin-top: 10px;
            font-size: 8px;
            text-align: right;
        }

        .summary {
            margin-top: 12px;
            width: 50%;
            float: right;
        }

        .summary td {
            font-weight: bold;
        }
    </style>
</head>
<body>

<h2>
    LAPORAN GAJI KARYAWAN<br>
    BULAN {{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }}
</h2>

@php
    $totalGajiKotor = 0;
    $totalPotongan  = 0;
    $totalBersih    = 0;
@endphp

<table>
    <thead>
        <tr>
            <th width="3%">No</th>
            <th width="6%">Toko</th>
            <th width="10%">Karyawan</th>
            <th width="4%">Hari</th>
            <th width="4%">Off</th>
            <th width="4%">Pres</th>
            <th width="6%">Gaji</th>
            <th width="5%">Umum</th>
            <th width="5%">Trans</th>
            <th width="5%">THR</th>
            <th width="5%">Kes</th>
            <th width="5%">/Hari</th>
            <th width="4%">Telat</th>
            <th width="6%">Lembur</th>
            <th width="4%">Menit</th>
            <th width="6%">Potong</th>
            <th width="6%">Salary</th>
            <th width="7%">Total</th>
        </tr>
    </thead>

    <tbody>
    @foreach($laporan as $row)

        @php
            $totalGajiKotor += $row['salary'];
            $totalPotongan  += $row['nominal_potongan_telat'];
            $totalBersih    += $row['total_gaji'];
        @endphp

        <tr>
            <td>{{ $row['no'] }}</td>
            <td>{{ $row['toko'] }}</td>

            <td class="text-left">
                {{ $row['nama'] }}
            </td>

            <td>{{ $row['jumlah_hari'] }}</td>
            <td>{{ $row['off_day'] }}</td>
            <td>{{ $row['presensi_masuk'] }}</td>

            <td class="text-right">{{ number_format($row['gaji_pokok'],0,',','.') }}</td>
            <td class="text-right">{{ number_format($row['tunjangan_umum'],0,',','.') }}</td>
            <td class="text-right">{{ number_format($row['tunjangan_transport'],0,',','.') }}</td>
            <td class="text-right">{{ number_format($row['tunjangan_hari_raya'],0,',','.') }}</td>
            <td class="text-right">{{ number_format($row['tunjangan_kesehatan'],0,',','.') }}</td>
            <td class="text-right">{{ number_format($row['hitungan_per_hari'],0,',','.') }}</td>
            <td>{{ $row['kerja_tidak_jam'] }}</td>
            <td class="text-right">{{ number_format($row['lembur_poin_lain'],0,',','.') }}</td>
            <td>{{ $row['potongan_n_telat'] }}</td>
            <td class="text-right">{{ number_format($row['nominal_potongan_telat'],0,',','.') }}</td>
            <td class="text-right">{{ number_format($row['salary'],0,',','.') }}</td>
            <td class="text-right"><strong>{{ number_format($row['total_gaji'],0,',','.') }}</strong></td>
        </tr>
    @endforeach
    </tbody>
</table>

{{-- ================= TOTAL KESELURUHAN ================= --}}
<table class="summary">
    <tr>
        <td>Total Salary Kotor</td>
        <td class="text-right">Rp {{ number_format($totalGajiKotor,0,',','.') }}</td>
    </tr>
    <tr>
        <td>Total Potongan Telat</td>
        <td class="text-right">Rp {{ number_format($totalPotongan,0,',','.') }}</td>
    </tr>
    <tr>
        <td>Total Gaji Dibayarkan</td>
        <td class="text-right">
            <strong>Rp {{ number_format($totalBersih,0,',','.') }}</strong>
        </td>
    </tr>
</table>

<div class="footer">
    Dicetak pada {{ now()->translatedFormat('d F Y H:i') }}
</div>

</body>
</html>
