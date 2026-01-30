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
            table-layout: fixed;
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
    $totalSalaryKotor = 0;
    $totalPotongan   = 0;
    $totalBersih     = 0;
@endphp

<table>
    <thead>
        <tr>
            <th width="3%">No</th>
            <th width="6%">Toko</th>
            <th width="12%">Karyawan</th>
            <th width="4%">HK</th>
            <th width="4%">Hadir</th>
            <th width="4%">Telat</th>
            <th width="4%">Off</th>
            <th width="6%">Gaji</th>
            <th width="5%">Umum</th>
            <th width="5%">Trans</th>
            <th width="5%">THR</th>
            <th width="5%">Kes</th>
            <th width="5%">/Hari</th>
            <th width="6%">Lembur</th>
            <th width="6%">Pot. Telat</th>
            <th width="7%">Salary Kotor</th>
            <th width="8%">Total Gaji</th>
        </tr>
    </thead>

    <tbody>
    @forelse($laporan as $row)

        @php
            $totalSalaryKotor += $row['salary_kotor'] ?? 0;
            $totalPotongan   += $row['potongan_telat'] ?? 0;
            $totalBersih     += $row['total_gaji'] ?? 0;
        @endphp

        <tr>
            <td>{{ $row['no'] }}</td>
            <td>{{ $row['toko'] ?? '-' }}</td>

            <td class="text-left">
                {{ $row['nama'] ?? '-' }}
            </td>

            <td>{{ $row['hari_kerja_standar'] ?? 26 }}</td>
            <td>{{ $row['hari_hadir'] ?? 0 }}</td>
            <td>{{ $row['hari_telat'] ?? 0 }}</td>
            <td>{{ $row['off_day'] ?? 0 }}</td>

            <td class="text-right">
                {{ number_format($row['gaji_pokok'] ?? 0,0,',','.') }}
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
                {{ number_format($row['gaji_per_hari'] ?? 0,0,',','.') }}
            </td>

            <td class="text-right">
                {{ number_format($row['lembur'] ?? 0,0,',','.') }}
            </td>

            <td class="text-right">
                {{ number_format($row['potongan_telat'] ?? 0,0,',','.') }}
            </td>

            <td class="text-right">
                {{ number_format($row['salary_kotor'] ?? 0,0,',','.') }}
            </td>

            <td class="text-right">
                <strong>{{ number_format($row['total_gaji'] ?? 0,0,',','.') }}</strong>
            </td>
        </tr>

    @empty
        <tr>
            <td colspan="17" style="text-align:center;">
                Tidak ada data laporan
            </td>
        </tr>
    @endforelse
    </tbody>
</table>

{{-- ================= TOTAL ================= --}}
<table class="summary">
    <tr>
        <td>Total Salary Kotor</td>
        <td class="text-right">
            Rp {{ number_format($totalSalaryKotor,0,',','.') }}
        </td>
    </tr>
    <tr>
        <td>Total Potongan Telat</td>
        <td class="text-right">
            Rp {{ number_format($totalPotongan,0,',','.') }}
        </td>
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
