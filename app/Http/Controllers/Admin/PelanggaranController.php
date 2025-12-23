<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PelanggaranLog;
use App\Models\User;
use App\Models\MasterPelanggaran;
use App\Models\MasterLokasi;
use Illuminate\Http\Request;

class PelanggaranController extends Controller
{
    public function index()
    {
        $data = PelanggaranLog::with('user')
            ->orderBy('tanggal','desc')
            ->get();

        return view('admin.pelanggaran.index', compact('data'));
    }

    public function create()
    {
        return view('admin.pelanggaran.create', [
            'users' => User::orderBy('name')->get(),
            'pelanggaran' => MasterPelanggaran::all(),
            'lokasi' => MasterLokasi::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'user_id' => 'required',
            'kode_pelanggaran' => 'required',
            'lokasi' => 'required',
            'kronologi' => 'nullable|string',
            'bukti' => 'nullable|file|max:2048',
            'tindakan' => 'nullable|string',
        ]);

        $user = User::findOrFail($request->user_id);
        $pelanggaran = MasterPelanggaran::where('kode', $request->kode_pelanggaran)->first();

        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $buktiPath = $request->file('bukti')->store('bukti-pelanggaran', 'public');
        }

        PelanggaranLog::create([
            'tanggal' => $request->tanggal,
            'user_id' => $user->id,
            'jabatan' => $user->jabatan,
            'lokasi' => $request->lokasi,
            'kode_pelanggaran' => $pelanggaran->kode,
            'jenis_pelanggaran' => $pelanggaran->nama,
            'kategori' => $pelanggaran->kategori,
            'kronologi' => $request->kronologi,
            'bukti' => $buktiPath,
            'tindakan' => $request->tindakan,
            'catatan' => $request->catatan,
            'penanggung_jawab' => auth()->user()->name,
        ]);

        return redirect()
            ->route('admin.pelanggaran.index')
            ->with('success','Pelanggaran berhasil dicatat');
    }

    public function show(User $user)
    {
        $data = PelanggaranLog::where('user_id',$user->id)->get();
        return view('admin.pelanggaran.riwayat', compact('user','data'));
    }
}
