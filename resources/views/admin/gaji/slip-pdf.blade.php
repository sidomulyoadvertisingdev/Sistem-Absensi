<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Gaji</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
            color: #222;
        }

        .header {
            background: #1f4fa3;
            color: #fff;
            padding: 22px 20px;
            text-align: center;
        }

        .company {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .header h1 {
            margin: 0;
            font-size: 22px;
            letter-spacing: 1px;
        }

        .container {
            padding: 22px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .info th,
        .info td {
            padding: 4px 6px;
        }

        .info th {
            font-weight: bold;
            text-align: left;
            width: 120px;
        }

        .section-title {
            background: #1f4fa3;
            color: #fff;
            padding: 7px 10px;
            font-weight: bold;
            font-size: 12px;
            margin-top: 12px;
        }

        .box {
            border: 1px solid #dcdcdc;
        }

        .box td,
        .box th {
            padding: 6px 8px;
            border-bottom: 1px solid #eee;
        }

        .box th {
            background: #f4f6fb;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .net-salary {
            margin-top: 16px;
            padding: 12px;
            border: 2px solid #1f4fa3;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
        }

        .sign {
            margin-top: 45px;
            width: 100%;
        }

        .sign td {
            text-align: center;
            padding-top: 45px;
        }

        .sign .name {
            margin-top: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>

{{-- HEADER --}}
<div class="header">
    <div class="company">CV. Sidomulyo Advertising</div>
    <h1>SLIP GAJI</h1>
    <small>Periode {{ $bulan }}</small>
</div>

<div class="container">

    {{-- INFO KARYAWAN --}}
    <table class="info">
        <tr>
            <th>Nama</th>
            <td>: {{ $user->name }}</td>
            <th>Periode</th>
            <td>: {{ $bulan }}</td>
        </tr>
        <tr>
            <th>NIK</th>
            <td>: {{ $user->nik ?? '-' }}</td>
            <th>Jabatan</th>
            <td>: {{ $user->jabatan ?? '-' }}</td>
        </tr>
        <tr>
            <th>Penempatan</th>
            <td>: {{ $user->penempatan ?? '-' }}</td>
            <th>Tanggal Cetak</th>
            <td>: {{ date('d F Y') }}</td>
        </tr>
    </table>

    {{-- PENERIMAAN --}}
    <div class="section-title">PENERIMAAN</div>
    <div class="box">
        <table>
            <tr>
                <td>Gaji Pokok (Bulanan)</td>
                <td class="right">
                    Rp {{ number_format($salary->gaji_pokok,0,',','.') }}
                </td>
            </tr>
            <tr>
                <td>Hitungan Per Hari</td>
                <td class="right">
                    Rp {{ number_format($gajiPerHari,0,',','.') }}
                </td>
            </tr>
            <tr>
                <td>Gaji Pokok Dibayar ({{ $hariHadir }} Hari)</td>
                <td class="right">
                    Rp {{ number_format($gajiPokokFix,0,',','.') }}
                </td>
            </tr>
            <tr>
                <td>Tunjangan Umum</td>
                <td class="right">
                    Rp {{ number_format($salary->tunjangan_umum,0,',','.') }}
                </td>
            </tr>
            <tr>
                <td>Tunjangan Transport</td>
                <td class="right">
                    Rp {{ number_format($salary->tunjangan_transport,0,',','.') }}
                </td>
            </tr>
            <tr>
                <td>Tunjangan Hari Raya</td>
                <td class="right">
                    Rp {{ number_format($salary->tunjangan_thr,0,',','.') }}
                </td>
            </tr>
            <tr>
                <td>Tunjangan Kesehatan</td>
                <td class="right">
                    Rp {{ number_format($salary->tunjangan_kesehatan,0,',','.') }}
                </td>
            </tr>
            <tr>
                <td>Lembur ({{ $totalJamLembur }} Jam)</td>
                <td class="right">
                    Rp {{ number_format($totalLembur,0,',','.') }}
                </td>
            </tr>
            <tr>
                <th>Total Bonus Job Todo</th>
                <th class="right">
                    Rp {{ number_format($totalBonusJob,0,',','.') }}
                </th>
            </tr>
        </table>
    </div>

    {{-- TOTAL GAJI --}}
    <div class="net-salary">
        TOTAL GAJI DITERIMA<br>
        Rp {{ number_format($totalGaji,0,',','.') }}
    </div>

    {{-- RINCIAN BONUS JOB --}}
    <div class="section-title">RINCIAN BONUS JOB TODO</div>
    <div class="box">
        <table>
            <tr>
                <th>Job</th>
                <th class="right">Bonus</th>
            </tr>
            @forelse($jobBonus as $job)
                <tr>
                    <td>{{ $job->title }}</td>
                    <td class="right">
                        Rp {{ number_format($job->bonus,0,',','.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="center">
                        Tidak ada bonus job
                    </td>
                </tr>
            @endforelse
        </table>
    </div>

    {{-- TTD --}}
    <table class="sign">
        <tr>
            <td>
                Disetujui oleh,<br>
                HRD<br>
                (__________________)<br>
                <span class="name">HRD</span>
            </td>
            <td>
                Diterima oleh,<br>
                {{ $user->name }}<br>
                (__________________)<br>
                <span class="name">{{ $user->name }}</span>
            </td>
        </tr>
    </table>

</div>
</body>
</html>
