<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Gaji extends Model
{
    use HasFactory;

    protected $table = 'gajis'; // sesuaikan nama tabel

    protected $fillable = [
        'user_id',
        'bulan',
        'tahun',
        'gaji_pokok',
        'lembur',
        'potongan',
        'total',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
