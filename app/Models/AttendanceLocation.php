<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceLocation extends Model
{
    /**
     * Radius maksimal yang boleh dipakai untuk absensi (meter).
     */
    public const MAX_RADIUS = 200;

    protected $fillable = [
        'nama',
        'alamat',
        'latitude',
        'longitude',
        'radius',
        'aktif',
    ];

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
        'radius'    => 'integer',
        'aktif'     => 'boolean',
    ];

    /**
     * User yang boleh absen di lokasi ini.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'attendance_location_user')
            ->withTimestamps();
    }

    /**
     * Absensi yang tercatat pada lokasi ini.
     */
    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class, 'attendance_location_id');
    }

    public function scopeActive($query)
    {
        return $query->where('aktif', true);
    }

    /**
     * Jarak (meter) dari koordinat yang diberikan ke titik lokasi ini.
     */
    public function distanceFrom(float $lat, float $lng): float
    {
        return self::haversineMeters(
            (float) $this->latitude,
            (float) $this->longitude,
            $lat,
            $lng
        );
    }

    /**
     * Hitung jarak dua koordinat (meter) dengan rumus haversine.
     */
    public static function haversineMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
