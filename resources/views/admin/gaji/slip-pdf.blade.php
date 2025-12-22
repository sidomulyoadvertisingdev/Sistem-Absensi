<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Gaji</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
        }

        .subtitle {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .info td {
            padding: 4px;
        }

        .salary th,
        .salary td {
            border: 1px solid #000;
            padding: 6px;
        }

        .salary th {
            background: #f2f2f2;
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .total {
            font-weight: bold;
            background: #eaeaea;
        }
    </style>
</head>
<body>

    <h2>SLIP GAJI KARYAWAN</h2>

    {{-- ❌ JANGAN PAKAI CARBON DI BLADE --}}
    {{-- ✅ LANGSUNG TAMPILKAN --}}
    <div class="subtitle">
        Bulan {{ $bulan }}
    </div>

    <table class="info">
        <tr>
            <td width="120">Nama</td>
            <td>: {{ $user->name }}</td>
        </tr>
        <tr>
            <td>NIK</td>
            <td>: {{ $user->nik }}</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>: {{ $user->jabatan }}</td>
        </tr>
        <tr>
            <td>Penempatan</td>
            <td>: {{ $user->penempatan }}</td>
        </tr>
    </table>

    <br>

    <table class="salary">
        <tr>
            <th>Gaji Pokok</th>
            <td class="right">
                Rp {{ number_format($salary->gaji_pokok) }}
            </td>
        </tr>
        <tr>
            <th>Uang Makan</th>
            <td class="right">
                Rp {{ number_format($salary->uang_makan) }}
            </td>
        </tr>
        <tr>
            <th>Transport</th>
            <td class="right">
                Rp {{ number_format($salary->transport) }}
            </td>
        </tr>
        <tr>
            <th>
                Lembur ({{ number_format($totalJamLembur, 1) }} Jam)
            </th>
            <td class="right">
                Rp {{ number_format($totalLembur) }}
            </td>
        </tr>
        <tr class="total">
            <th>TOTAL GAJI</th>
            <td class="right">
                Rp {{ number_format($totalGaji) }}
            </td>
        </tr>
    </table>

    <br><br>

    <table width="100%">
        <tr>
            <td width="60%"></td>
            <td align="center">
                {{ now()->translatedFormat('d F Y') }}<br>
                HRD<br><br><br>
                (____________________)
            </td>
        </tr>
    </table>

</body>
</html>
