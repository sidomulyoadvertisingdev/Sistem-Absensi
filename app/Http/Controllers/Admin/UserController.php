<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | KARYAWAN CRUD
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $search = $request->query('search');

        $users = User::where('role', User::ROLE_KARYAWAN)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                       ->orWhere('nik', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get();

        return view('admin.karyawan.index', compact('users', 'search'));
    }

    public function create()
    {
        return view('admin.karyawan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'nik'        => 'required|string|max:30|unique:users,nik',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'required|string|max:20',
            'address'    => 'required|string',
            'jabatan'    => 'required|string|max:100',
            'penempatan' => 'required|string|max:100',
            'password'   => 'required|string|min:6',
        ]);

        User::create([
            'name'       => $validated['name'],
            'nik'        => $validated['nik'],
            'email'      => $validated['email'],
            'phone'      => $validated['phone'],
            'address'    => $validated['address'],
            'jabatan'    => $validated['jabatan'],
            'penempatan' => $validated['penempatan'],
            'role'       => User::ROLE_KARYAWAN,
            'password'   => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('admin.karyawan.index')
            ->with('success', 'Karyawan berhasil ditambahkan');
    }

    public function edit($id)
    {
        $user = User::where('role', User::ROLE_KARYAWAN)->findOrFail($id);
        return view('admin.karyawan.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::where('role', User::ROLE_KARYAWAN)->findOrFail($id);

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'nik'        => 'required|string|max:30|unique:users,nik,' . $user->id,
            'email'      => 'required|email|unique:users,email,' . $user->id,
            'phone'      => 'required|string|max:20',
            'address'    => 'required|string',
            'jabatan'    => 'required|string|max:100',
            'penempatan' => 'required|string|max:100',
            'password'   => 'nullable|string|min:6',
        ]);

        $data = collect($validated)->except('password')->toArray();

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()
            ->route('admin.karyawan.index')
            ->with('success', 'Data karyawan berhasil diperbarui');
    }

    public function destroy($id)
    {
        User::where('role', User::ROLE_KARYAWAN)
            ->findOrFail($id)
            ->delete();

        return redirect()
            ->route('admin.karyawan.index')
            ->with('success', 'Data karyawan berhasil dihapus');
    }

    /*
    |--------------------------------------------------------------------------
    | 🔥 EXPORT CSV (LARAVEL 12 – NATIVE)
    |--------------------------------------------------------------------------
    */

    public function exportCsv(): StreamedResponse
    {
        $filename = 'data-karyawan-' . now()->format('Ymd_His') . '.csv';

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');

            // HEADER CSV (sesuai Blade & import)
            fputcsv($handle, [
                'Nama',
                'NIK',
                'Email',
                'No HP',
                'Alamat',
                'Jabatan',
                'Penempatan',
                'Tanggal Daftar',
            ]);

            User::where('role', User::ROLE_KARYAWAN)
                ->orderBy('name')
                ->chunk(500, function ($users) use ($handle) {
                    foreach ($users as $user) {
                        fputcsv($handle, [
                            $user->name,
                            $user->nik,
                            $user->email,
                            $user->phone,
                            $user->address,
                            $user->jabatan,
                            $user->penempatan,
                            optional($user->created_at)->format('d-m-Y'),
                        ]);
                    }
                });

            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 🔥 IMPORT CSV (LARAVEL 12 – NATIVE)
    |--------------------------------------------------------------------------
    */

    public function importCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $path = $request->file('file')->getRealPath();
        $file = fopen($path, 'r');

        // Skip header
        fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {

            // Wajib minimal 7 kolom
            if (count($row) < 7) {
                continue;
            }

            // Email wajib
            if (empty($row[2])) {
                continue;
            }

            User::updateOrCreate(
                ['email' => trim($row[2])],
                [
                    'name'       => trim($row[0]),
                    'nik'        => trim($row[1]),
                    'phone'      => trim($row[3]),
                    'address'    => trim($row[4]),
                    'jabatan'    => trim($row[5]),
                    'penempatan' => trim($row[6]),
                    'role'       => User::ROLE_KARYAWAN,
                    // default password (bisa diganti nanti)
                    'password'   => Hash::make('password123'),
                ]
            );
        }

        fclose($file);

        return redirect()
            ->route('admin.karyawan.index')
            ->with('success', 'Import CSV berhasil');
    }

    /*
    |--------------------------------------------------------------------------
    | USER ROLE MANAGEMENT (PROMOTE / DEMOTE)
    |--------------------------------------------------------------------------
    */

    public function allUsers()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return view('admin.users.index', compact('users'));
    }

    public function promoteToKaryawan(User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('warning', 'Admin tidak bisa diubah');
        }

        if ($user->isKaryawan()) {
            return back()->with('warning', 'User sudah menjadi karyawan');
        }

        $user->update([
            'role' => User::ROLE_KARYAWAN,
        ]);

        return back()->with('success', 'User berhasil dipromosikan menjadi Karyawan');
    }

    public function demoteToUser(User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Admin tidak bisa diturunkan');
        }

        if ($user->isUser()) {
            return back()->with('warning', 'User sudah berstatus user biasa');
        }

        $user->update([
            'role'       => User::ROLE_USER,
            'nik'        => null,
            'jabatan'    => null,
            'penempatan' => null,
        ]);

        return back()->with('success', 'Karyawan berhasil diturunkan menjadi User');
    }
}
