<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;

class UserScheduleController extends Controller
{
    public function index()
    {
        $users = User::with('workSchedule')->get();
        return view('admin.jadwal.index', compact('users'));
    }

    public function edit(User $user)
    {
        return view('admin.jadwal.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'jam_masuk'         => 'required',
            'jam_pulang'        => 'required',
            'istirahat_mulai'   => 'required',
            'istirahat_selesai' => 'required',
        ]);

        WorkSchedule::updateOrCreate(
            ['user_id' => $user->id],
            [
                'jam_masuk'         => $request->jam_masuk,
                'jam_pulang'        => $request->jam_pulang,
                'istirahat_mulai'   => $request->istirahat_mulai,
                'istirahat_selesai' => $request->istirahat_selesai,
                'aktif'             => $request->has('aktif'),
            ]
        );

        return redirect()
            ->route('admin.jadwal')
            ->with('success', 'Jadwal kerja user berhasil disimpan');
    }
}
