<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensis';

    /**
     * Kolom yang boleh diisi
     */
    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_masuk',
        'istirahat_mulai',
        'istirahat_selesai',
        'jam_pulang',
        'foto',
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
    ];

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
