<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLocation;
use App\Models\User;
use Illuminate\Http\Request;

class AttendanceLocationController extends Controller
{
    /**
     * ===============================
     * LIST LOKASI ABSENSI
     * ===============================
     */
    public function index()
    {
        $locations = AttendanceLocation::withCount('users')
            ->orderBy('nama')
            ->get();

        return view('admin.lokasi-absensi.index', compact('locations'));
    }

    /**
     * ===============================
     * FORM TAMBAH
     * ===============================
     */
    public function create()
    {
        $location = new AttendanceLocation([
            'radius' => AttendanceLocation::MAX_RADIUS,
            'aktif'  => true,
        ]);

        return view('admin.lokasi-absensi.create', [
            'location'      => $location,
            'karyawan'      => $this->karyawanOptions(),
            'selectedUsers' => [],
        ]);
    }

    /**
     * ===============================
     * SIMPAN
     * ===============================
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $location = AttendanceLocation::create($data);
        $location->users()->sync($request->input('users', []));

        return redirect()
            ->route('admin.lokasi-absensi.index')
            ->with('success', 'Lokasi absensi berhasil ditambahkan.');
    }

    /**
     * ===============================
     * FORM EDIT
     * ===============================
     */
    public function edit(AttendanceLocation $lokasi_absensi)
    {
        return view('admin.lokasi-absensi.edit', [
            'location'      => $lokasi_absensi,
            'karyawan'      => $this->karyawanOptions(),
            'selectedUsers' => $lokasi_absensi->users()->pluck('users.id')->all(),
        ]);
    }

    /**
     * ===============================
     * UPDATE
     * ===============================
     */
    public function update(Request $request, AttendanceLocation $lokasi_absensi)
    {
        $data = $this->validateData($request);

        $lokasi_absensi->update($data);
        $lokasi_absensi->users()->sync($request->input('users', []));

        return redirect()
            ->route('admin.lokasi-absensi.index')
            ->with('success', 'Lokasi absensi berhasil diperbarui.');
    }

    /**
     * ===============================
     * HAPUS
     * ===============================
     */
    public function destroy(AttendanceLocation $lokasi_absensi)
    {
        $lokasi_absensi->delete();

        return back()->with('success', 'Lokasi absensi berhasil dihapus.');
    }

    /**
     * Validasi input form lokasi.
     */
    private function validateData(Request $request): array
    {
        $validated = $request->validate([
            'nama'      => 'required|string|max:150',
            'alamat'    => 'nullable|string|max:255',
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius'    => 'required|integer|min:10|max:' . AttendanceLocation::MAX_RADIUS,
            'users'     => 'array',
            'users.*'   => 'integer|exists:users,id',
        ], [
            'radius.max' => 'Radius maksimal ' . AttendanceLocation::MAX_RADIUS . ' meter.',
        ]);

        return [
            'nama'      => $validated['nama'],
            'alamat'    => $validated['alamat'] ?? null,
            'latitude'  => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'radius'    => $validated['radius'],
            'aktif'     => $request->boolean('aktif'),
        ];
    }

    /**
     * Daftar karyawan untuk di-assign ke lokasi.
     */
    private function karyawanOptions()
    {
        return User::whereIn('role', [User::ROLE_KARYAWAN])
            ->orderBy('name')
            ->get(['id', 'name', 'nik']);
    }
}
