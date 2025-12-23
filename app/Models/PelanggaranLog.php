<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PelanggaranLog extends Model
{
    protected $fillable = [
        'tanggal',
        'user_id',
        'jabatan',
        'lokasi',
        'kode_pelanggaran',
        'jenis_pelanggaran',
        'kategori',
        'kronologi',
        'bukti',
        'tindakan',
        'catatan',
        'penanggung_jawab',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
