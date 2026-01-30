<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Slip Gaji</title>
</head>
<body style="font-family: Arial, sans-serif; line-height:1.6; color:#333;">

    <h2 style="margin-bottom:0;">Slip Gaji Karyawan</h2>
    <p style="margin-top:4px;">
        Periode: <strong>{{ $periode }}</strong>
    </p>

    <hr>

    <p>Halo <strong>{{ $user->name }}</strong>,</p>

    <p>
        Berikut kami lampirkan <strong>Slip Gaji</strong> Anda untuk periode
        <strong>{{ $periode }}</strong>.
    </p>

    <p>
        Jika terdapat ketidaksesuaian data, silakan segera menghubungi
        bagian HRD / Admin.
    </p>

    <br>

    <p>Terima kasih atas dedikasi dan kinerja Anda.</p>

    <br>

    <p style="margin-bottom:0;">
        Hormat kami,
    </p>
    <strong>HRD</strong><br>
    <small>{{ config('app.name') }}</small>

    <hr>

    <small style="color:#777;">
        Email ini dikirim otomatis oleh sistem.  
        Mohon tidak membalas email ini.
    </small>

</body>
</html>
