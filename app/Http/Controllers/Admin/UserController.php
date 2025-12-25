<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * LIST & SEARCH KARYAWAN
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $users = User::where('role', 'karyawan')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('nik', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get();

        return view('admin.karyawan.index', compact('users', 'search'));
    }

    /**
     * FORM CREATE
     */
    public function create()
    {
        return view('admin.karyawan.create');
    }

    /**
     * STORE DATA
     */
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
            'role'       => 'karyawan',
            'password'   => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('admin.karyawan.index')
            ->with('success', 'Karyawan berhasil ditambahkan');
    }

    /**
     * FORM EDIT
     */
    public function edit($id)
    {
        $user = User::where('role', 'karyawan')->findOrFail($id);

        return view('admin.karyawan.edit', compact('user'));
    }

    /**
     * UPDATE DATA
     */
    public function update(Request $request, $id)
    {
        $user = User::where('role', 'karyawan')->findOrFail($id);

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

        $data = [
            'name'       => $validated['name'],
            'nik'        => $validated['nik'],
            'email'      => $validated['email'],
            'phone'      => $validated['phone'],
            'address'    => $validated['address'],
            'jabatan'    => $validated['jabatan'],
            'penempatan' => $validated['penempatan'],
        ];

        // Password hanya diupdate jika diisi
        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()
            ->route('admin.karyawan.index')
            ->with('success', 'Data karyawan berhasil diperbarui');
    }

    /**
     * HAPUS DATA
     */
    public function destroy($id)
    {
        $user = User::where('role', 'karyawan')->findOrFail($id);
        $user->delete();

        return redirect()
            ->route('admin.karyawan.index')
            ->with('success', 'Data karyawan berhasil dihapus');
    }
}
