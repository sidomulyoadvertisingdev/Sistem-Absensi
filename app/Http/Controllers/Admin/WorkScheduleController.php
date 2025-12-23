<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;

class WorkScheduleController extends Controller
{
    /**
     * =======================================
     * LIST USER + JADWAL KERJA
     * =======================================
     */
    public function index()
    {
        $users = User::with('workSchedules')
            ->orderBy('name')
            ->get();

        return view('admin.jadwal.index', compact('users'));
    }

    /**
     * =======================================
     * FORM EDIT JADWAL KERJA PER USER
     * =======================================
     */
    public function edit(User $user)
    {
        $hariList = [
            'senin',
            'selasa',
            'rabu',
            'kamis',
            'jumat',
            'sabtu',
            'minggu',
        ];

        // Ambil jadwal user dan key berdasarkan hari
        $jadwal = $user->workSchedules->keyBy('hari');

        return view('admin.jadwal.edit', compact(
            'user',
            'hariList',
            'jadwal'
        ));
    }

    /**
     * =======================================
     * SIMPAN / UPDATE JADWAL KERJA
     * =======================================
     */
    public function update(Request $request, User $user)
    {
        $hariList = [
            'senin',
            'selasa',
            'rabu',
            'kamis',
            'jumat',
            'sabtu',
            'minggu',
        ];

        foreach ($hariList as $hari) {

            /**
             * ---------------------------------------
             * JIKA HARI TIDAK DICENTANG → LIBUR
             * ---------------------------------------
             */
            if (!$request->has("hari.$hari")) {

                WorkSchedule::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'hari'    => $hari,
                    ],
                    [
                        'jam_masuk'         => null,
                        'jam_pulang'        => null,
                        'istirahat_mulai'   => null,
                        'istirahat_selesai' => null,
                        'aktif'             => false,
                    ]
                );

                continue;
            }

            /**
             * ---------------------------------------
             * JIKA HARI DICENTANG → VALIDASI JAM
             * ---------------------------------------
             */
            $request->validate([
                "jam_masuk.$hari"         => 'required',
                "jam_pulang.$hari"        => 'required',
                "istirahat_mulai.$hari"   => 'required',
                "istirahat_selesai.$hari" => 'required',
            ], [
                "jam_masuk.$hari.required"         => "Jam masuk hari $hari wajib diisi",
                "jam_pulang.$hari.required"        => "Jam pulang hari $hari wajib diisi",
                "istirahat_mulai.$hari.required"   => "Istirahat mulai hari $hari wajib diisi",
                "istirahat_selesai.$hari.required" => "Istirahat selesai hari $hari wajib diisi",
            ]);

            /**
             * ---------------------------------------
             * SIMPAN / UPDATE JADWAL HARI KERJA
             * ---------------------------------------
             */
            WorkSchedule::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'hari'    => $hari,
                ],
                [
                    'jam_masuk'         => $request->jam_masuk[$hari],
                    'jam_pulang'        => $request->jam_pulang[$hari],
                    'istirahat_mulai'   => $request->istirahat_mulai[$hari],
                    'istirahat_selesai' => $request->istirahat_selesai[$hari],
                    'aktif'             => true,
                ]
            );
        }

        return redirect()
            ->route('admin.jadwal')
            ->with('success', 'Jadwal kerja berhasil disimpan');
    }
}
