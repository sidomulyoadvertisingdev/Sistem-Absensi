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
        'jam_pulang',
        'foto',
        'status',
        'terlambat'
    ];

    /**
     * Casting tipe data
     */
    protected $casts = [
        'tanggal'   => 'date',
        'jam_masuk' => 'datetime:H:i:s',
        'jam_pulang'=> 'datetime:H:i:s',
    ];

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
