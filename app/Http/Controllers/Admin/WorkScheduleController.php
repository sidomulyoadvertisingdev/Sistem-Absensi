<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Models\WorkScheduleDate;
use Carbon\Carbon;
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

        // Pastikan semua hari ada record di DB (tidak mengubah data lama)
        foreach ($hariList as $hari) {
            WorkSchedule::firstOrCreate(
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
        }

        $jadwal = WorkSchedule::where('user_id', $user->id)
            ->get()
            ->keyBy('hari');

        $jadwalTanggal = WorkScheduleDate::where('user_id', $user->id)
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('admin.jadwal.edit', compact(
            'user',
            'hariList',
            'jadwal',
            'jadwalTanggal'
        ));
    }

    /**
     * =======================================
     * SIMPAN / UPDATE JADWAL KERJA
     * =======================================
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'schedule_mode' => 'required|in:per_hari,per_tanggal',
        ]);

        $user->update([
            'schedule_mode' => $request->schedule_mode,
        ]);

        $hariList = [
            'senin',
            'selasa',
            'rabu',
            'kamis',
            'jumat',
            'sabtu',
            'minggu',
        ];

        if ($request->schedule_mode === 'per_hari') {
            foreach ($hariList as $hari) {
                // Jika hari tidak dicentang -> libur
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
        }

        if ($request->schedule_mode === 'per_tanggal') {
            $tanggalList = $request->input('tanggal_tgl', []);

            foreach ($tanggalList as $key => $tanggalRaw) {
                if (empty($tanggalRaw)) {
                    continue;
                }

                $request->validate([
                    "tanggal_tgl.$key" => 'required|date',
                ]);

                $tanggal = Carbon::parse($tanggalRaw)->toDateString();

                if ($request->boolean("hapus_tgl.$key")) {
                    WorkScheduleDate::where('user_id', $user->id)
                        ->where('tanggal', $tanggal)
                        ->delete();
                    continue;
                }

                $aktif = $request->boolean("aktif_tgl.$key");

                if ($aktif) {
                    $request->validate([
                        "jam_masuk_tgl.$key"         => 'required|date_format:H:i',
                        "jam_pulang_tgl.$key"        => 'required|date_format:H:i',
                        "istirahat_mulai_tgl.$key"   => 'required|date_format:H:i',
                        "istirahat_selesai_tgl.$key" => 'required|date_format:H:i',
                    ], [
                        "jam_masuk_tgl.$key.required"         => "Jam masuk tanggal $tanggal wajib diisi",
                        "jam_pulang_tgl.$key.required"        => "Jam pulang tanggal $tanggal wajib diisi",
                        "istirahat_mulai_tgl.$key.required"   => "Istirahat mulai tanggal $tanggal wajib diisi",
                        "istirahat_selesai_tgl.$key.required" => "Istirahat selesai tanggal $tanggal wajib diisi",
                    ]);
                }

                WorkScheduleDate::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'tanggal' => $tanggal,
                    ],
                    [
                        'jam_masuk'         => $aktif ? $request->input("jam_masuk_tgl.$key") : null,
                        'jam_pulang'        => $aktif ? $request->input("jam_pulang_tgl.$key") : null,
                        'istirahat_mulai'   => $aktif ? $request->input("istirahat_mulai_tgl.$key") : null,
                        'istirahat_selesai' => $aktif ? $request->input("istirahat_selesai_tgl.$key") : null,
                        'aktif'             => $aktif,
                    ]
                );
            }
        }

        return redirect()
            ->route('admin.jadwal')
            ->with('success', 'Jadwal kerja berhasil disimpan');
    }
}
