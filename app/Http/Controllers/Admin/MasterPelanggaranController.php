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
}
