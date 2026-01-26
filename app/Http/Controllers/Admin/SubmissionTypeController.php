<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubmissionType;
use Illuminate\Http\Request;

class SubmissionTypeController extends Controller
{
    public function index()
    {
        $types = SubmissionType::orderBy('nama')->get();
        return view('admin.submission-types.index', compact('types'));
    }

    public function create()
    {
        return view('admin.submission-types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|max:50|unique:submission_types,kode',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'butuh_alasan' => 'nullable|boolean',
            'butuh_lampiran' => 'nullable|boolean',
        ]);

        SubmissionType::create([
            'kode' => strtoupper($request->kode),
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'butuh_alasan' => $request->boolean('butuh_alasan'),
            'butuh_lampiran' => $request->boolean('butuh_lampiran'),
            'aktif' => true,
        ]);

        return redirect()
            ->route('admin.submission-types.index')
            ->with('success', 'Jenis pengajuan berhasil ditambahkan');
    }

    public function edit(SubmissionType $type)
    {
        return view('admin.submission-types.edit', compact('type'));
    }

    public function update(Request $request, SubmissionType $type)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'butuh_alasan' => 'nullable|boolean',
            'butuh_lampiran' => 'nullable|boolean',
        ]);

        $type->update([
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'butuh_alasan' => $request->boolean('butuh_alasan'),
            'butuh_lampiran' => $request->boolean('butuh_lampiran'),
        ]);

        return redirect()
            ->route('admin.submission-types.index')
            ->with('success', 'Jenis pengajuan berhasil diperbarui');
    }

    public function destroy(SubmissionType $type)
    {
        $type->delete();

        return redirect()
            ->route('admin.submission-types.index')
            ->with('success', 'Jenis pengajuan berhasil dihapus');
    }

    public function toggle(SubmissionType $type)
    {
        $type->update([
            'aktif' => !$type->aktif
        ]);

        return redirect()
            ->route('admin.submission-types.index')
            ->with('success', 'Status berhasil diubah');
    }
}
