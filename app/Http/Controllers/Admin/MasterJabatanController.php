<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterJabatan;
use Illuminate\Http\Request;

class MasterJabatanController extends Controller
{
    /**
     * ===============================
     * LIST MASTER JABATAN
     * ===============================
     */
    public function index()
    {
        $data = MasterJabatan::orderBy('nama')->get();

        return view('admin.pelanggaran.master.jabatan.index', compact('data'));
    }

    /**
     * ===============================
     * FORM TAMBAH JABATAN
     * ===============================
     */
    public function create()
    {
        return view('admin.pelanggaran.master.jabatan.create');
    }

    /**
     * ===============================
     * SIMPAN JABATAN
     * ===============================
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:master_jabatans,nama',
        ]);

        MasterJabatan::create([
            'nama'       => $request->nama,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()
            ->route('admin.pelanggaran.master.jabatan.index')
            ->with('success', 'Jabatan berhasil ditambahkan');
    }

    /**
     * ===============================
     * FORM EDIT JABATAN
     * ===============================
     */
    public function edit(MasterJabatan $jabatan)
    {
        return view('admin.pelanggaran.master.jabatan.edit', compact('jabatan'));
    }

    /**
     * ===============================
     * UPDATE JABATAN
     * ===============================
     */
    public function update(Request $request, MasterJabatan $jabatan)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:master_jabatans,nama,' . $jabatan->id,
        ]);

        $jabatan->update([
            'nama'       => $request->nama,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()
            ->route('admin.pelanggaran.master.jabatan.index')
            ->with('success', 'Jabatan berhasil diperbarui');
    }

    /**
     * ===============================
     * HAPUS JABATAN
     * ===============================
     */
    public function destroy(MasterJabatan $jabatan)
    {
        $jabatan->delete();

        return redirect()
            ->route('admin.pelanggaran.master.jabatan.index')
            ->with('success', 'Jabatan berhasil dihapus');
    }
}
