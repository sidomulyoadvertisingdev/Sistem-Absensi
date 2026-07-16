<?php

namespace App\Events;

use App\Models\Absensi;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceRecorded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Absensi $absensi,
        public User $user,
        public string $aksi
    ) {
    }
}
