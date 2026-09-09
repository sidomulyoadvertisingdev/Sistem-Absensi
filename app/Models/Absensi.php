<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensis';

    /**
     * Kirim URL foto siap-pakai ke frontend.
     */
    protected $appends = ['foto_url'];

    /**
     * Kolom yang boleh diisi
     */
    protected $fillable = [
        'user_id',
        'attendance_location_id',
        'tanggal',
        'jam_masuk',
        'lat_masuk',
        'lng_masuk',
        'istirahat_mulai',
        'istirahat_selesai',
        'jam_pulang',
        'lat_pulang',
        'lng_pulang',
        'foto',
        'foto_backed_up',
        'status',
        'terlambat',
        'menit_terlambat',
        'locked'
    ];

    /**
     * Casting tipe data
     */
    protected $casts = [
        'tanggal'   => 'date',
        // kolom TIME, jangan dicast ke datetime agar tidak error parsing
        'jam_masuk' => 'string',
        'jam_pulang'=> 'string',
        'lat_masuk'  => 'float',
        'lng_masuk'  => 'float',
        'lat_pulang' => 'float',
        'lng_pulang' => 'float',
    ];

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Lokasi absensi yang dipakai (geofence).
     */
    public function attendanceLocation()
    {
        return $this->belongsTo(AttendanceLocation::class, 'attendance_location_id');
    }

    /**
     * URL publik foto absensi.
     * Kolom `foto` menyimpan path relatif (mis. "absensi/xxxx.jpg") hasil
     * store('absensi', 'public'). Butuh `php artisan storage:link` sekali.
     */
    public function getFotoUrlAttribute(): ?string
    {
        $foto = $this->foto;

        if (!$foto) {
            return null;
        }

        if (Str::startsWith($foto, ['http://', 'https://'])) {
            return $foto;
        }

        if (Str::startsWith($foto, 'storage/')) {
            return asset($foto);
        }

        return asset('storage/' . ltrim($foto, '/'));
    }
}
