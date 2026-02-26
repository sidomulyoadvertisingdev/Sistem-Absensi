<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubmissionType extends Model
{
    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'butuh_alasan',
        'butuh_lampiran',
        'is_izin_pulang_awal',
        'aktif',
    ];

    protected $casts = [
        'butuh_alasan' => 'boolean',
        'butuh_lampiran' => 'boolean',
        'is_izin_pulang_awal' => 'boolean',
        'aktif' => 'boolean',
    ];
}
