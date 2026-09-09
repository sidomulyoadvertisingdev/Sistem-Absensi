<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterPelanggaran;
use Illuminate\Http\Request;

class MasterPelanggaranController extends Controller
{
    public function index()
    {
        $data = MasterPelanggaran::orderBy('kode')->get();
        return view('admin.pelanggaran.master.kode.index', compact('data'));
    }

    public function create()
    {
        return view('admin.pelanggaran.master.kode.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode'     => 'required|unique:master_pelanggarans,kode',
            'kategori' => 'required|in:Ringan,Sedang,Berat',
            'nama'     => 'required',
        ]);

        MasterPelanggaran::create([
            'kode'     => $request->kode,
            'kategori' => $request->kategori,
            'nama'     => $request->nama,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()
            ->route('admin.pelanggaran.master.kode.index')
            ->with('success', 'Kode pelanggaran berhasil ditambahkan');
    }

    public function edit(MasterPelanggaran $kode)
    {
        return view('admin.pelanggaran.master.kode.edit', compact('kode'));
    }

    public function update(Request $request, MasterPelanggaran $kode)
    {
        $request->validate([
            'kode'     => 'required|unique:master_pelanggarans,kode,' . $kode->id,
            'kategori' => 'required|in:Ringan,Sedang,Berat',
            'nama'     => 'required',
        ]);

        $kode->update([
            'kode'     => $request->kode,
            'kategori' => $request->kategori,
            'nama'     => $request->nama,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()
            ->route('admin.pelanggaran.master.kode.index')
            ->with('success', 'Kode pelanggaran berhasil diperbarui');
    }

    public function destroy(MasterPelanggaran $kode)
    {
        $kode->delete();

        return redirect()
            ->route('admin.pelanggaran.master.kode.index')
            ->with('success', 'Kode pelanggaran berhasil dihapus');
    }
}
