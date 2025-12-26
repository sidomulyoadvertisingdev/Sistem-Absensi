<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        // contoh data (nanti bisa real query)
        $absensiToday = $user->absensis()
            ->whereDate('created_at', $today)
            ->first();

        $jadwalToday = $user->workSchedules()
            ->where('day', $today->format('l')) // Monday, Tuesday, etc
            ->first();

        $salary = $user->salary;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'jabatan' => $user->jabatan,
            ],
            'absensi' => $absensiToday ? [
                'status' => 'Sudah Absen',
                'jam' => $absensiToday->created_at->format('H:i'),
            ] : [
                'status' => 'Belum Absen',
                'jam' => null,
            ],
            'jadwal' => $jadwalToday ? [
                'masuk' => $jadwalToday->start_time,
                'pulang' => $jadwalToday->end_time,
            ] : null,
            'gaji' => $salary ? [
                'bulan' => now()->format('F Y'),
                'total' => $salary->total_salary,
            ] : null,
        ]);
    }
}
