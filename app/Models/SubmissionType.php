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
        'aktif',
    ];
}
