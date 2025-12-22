<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * LIST & SEARCH KARYAWAN
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $users = User::when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('nik', 'like', "%{$search}%");
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
     * STORE
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'nik'        => 'required|string|max:30|unique:users,nik',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'required|string|max:20',
            'address'    => 'required|string',
            'jabatan'    => 'required|string|max:100',
            'penempatan' => 'required|string',
            'password'   => 'required|string|min:6',
        ]);

        User::create([
            'name'       => $request->name,
            'nik'        => $request->nik,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'address'    => $request->address,
            'jabatan'    => $request->jabatan,
            'penempatan' => $request->penempatan,
            'role'       => 'karyawan',
            'password'   => bcrypt($request->password),
        ]);

        return redirect()
            ->route('admin.karyawan.index')
            ->with('success', 'Karyawan berhasil ditambahkan');
    }
}
