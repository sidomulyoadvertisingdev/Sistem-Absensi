<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterLokasi;
use Illuminate\Http\Request;

class MasterLokasiController extends Controller
{
    /**
     * ===============================
     * LIST MASTER LOKASI
     * ===============================
     */
    public function index()
    {
        $data = MasterLokasi::orderBy('nama')->get();

        return view(
            'admin.pelanggaran.master.lokasi.index',
            compact('data')
        );
    }

    /**
     * ===============================
     * FORM TAMBAH LOKASI
     * ===============================
     */
    public function create()
    {
        return view('admin.pelanggaran.master.lokasi.create');
    }

    /**
     * ===============================
     * SIMPAN LOKASI
     * ===============================
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:master_lokasis,nama',
            'keterangan' => 'nullable|string|max:255',
        ]);

        MasterLokasi::create([
            'nama'       => $request->nama,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()
            ->route('admin.pelanggaran.master.lokasi.index')
            ->with('success', 'Lokasi berhasil ditambahkan');
    }

    /**
     * ===============================
     * FORM EDIT LOKASI
     * ===============================
     */
    public function edit(MasterLokasi $lokasi)
    {
        return view(
            'admin.pelanggaran.master.lokasi.edit',
            compact('lokasi')
        );
    }

    /**
     * ===============================
     * UPDATE LOKASI
     * ===============================
     */
    public function update(Request $request, MasterLokasi $lokasi)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:master_lokasis,nama,' . $lokasi->id,
            'keterangan' => 'nullable|string|max:255',
        ]);

        $lokasi->update([
            'nama'       => $request->nama,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()
            ->route('admin.pelanggaran.master.lokasi.index')
            ->with('success', 'Lokasi berhasil diperbarui');
    }

    /**
     * ===============================
     * HAPUS LOKASI
     * ===============================
     */
    public function destroy(MasterLokasi $lokasi)
    {
        $lokasi->delete();

        return back()->with('success', 'Lokasi berhasil dihapus');
    }
}
