<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalaryDeductionRule;
use App\Models\User;
use Illuminate\Http\Request;

class SalaryDeductionRuleController extends Controller
{
    /**
     * ===============================
     * LIST ATURAN POTONGAN GAJI
     * ===============================
     */
    public function index()
    {
        $rules = SalaryDeductionRule::orderBy('nama')->get();

        return view('admin.potongan-gaji.index', compact('rules'));
    }

    /**
     * ===============================
     * FORM TAMBAH ATURAN
     * ===============================
     */
    public function create()
    {
        // 🔥 ambil penempatan dari user (distinct)
        $penempatans = User::whereNotNull('penempatan')
            ->distinct()
            ->orderBy('penempatan')
            ->pluck('penempatan');

        return view('admin.potongan-gaji.create', compact('penempatans'));
    }

    /**
     * ===============================
     * SIMPAN DATA
     * ===============================
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|max:50|unique:salary_deduction_rules,kode',
            'nama' => 'required|string|max:255',
            'keterangan' => 'nullable|string',

            // SISTEM POTONGAN
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'base_amount' => 'required|in:gaji_pokok,salary_kotor,total_gaji',

            // KONDISI
            'condition_type' => 'required|in:pelanggaran,off_day,terlambat',
            'condition_value' => 'nullable|integer|min:0',

            'aktif' => 'nullable|boolean',
        ]);

        SalaryDeductionRule::create([
            'kode' => strtoupper($request->kode),
            'nama' => $request->nama,
            'keterangan' => $request->keterangan,

            'type' => $request->type,
            'value' => $request->value,
            'base_amount' => $request->base_amount,

            'condition_type' => $request->condition_type,
            'condition_value' => $request->condition_value ?? 0,

            'aktif' => $request->boolean('aktif'),
        ]);

        return redirect()
            ->route('admin.potongan-gaji.index')
            ->with('success', 'Aturan potongan gaji berhasil ditambahkan');
    }

    /**
     * ===============================
     * FORM EDIT
     * ===============================
     */
    public function edit(SalaryDeductionRule $rule)
    {
        $penempatans = User::whereNotNull('penempatan')
            ->distinct()
            ->orderBy('penempatan')
            ->pluck('penempatan');

        return view('admin.potongan-gaji.edit', compact('rule', 'penempatans'));
    }

    /**
     * ===============================
     * UPDATE DATA
     * ===============================
     */
    public function update(Request $request, SalaryDeductionRule $rule)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'keterangan' => 'nullable|string',

            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'base_amount' => 'required|in:gaji_pokok,salary_kotor,total_gaji',

            'condition_type' => 'required|in:pelanggaran,off_day,terlambat',
            'condition_value' => 'nullable|integer|min:0',

            'aktif' => 'nullable|boolean',
        ]);

        $rule->update([
            'nama' => $request->nama,
            'keterangan' => $request->keterangan,

            'type' => $request->type,
            'value' => $request->value,
            'base_amount' => $request->base_amount,

            'condition_type' => $request->condition_type,
            'condition_value' => $request->condition_value ?? 0,

            'aktif' => $request->boolean('aktif'),
        ]);

        return redirect()
            ->route('admin.potongan-gaji.index')
            ->with('success', 'Aturan potongan gaji berhasil diperbarui');
    }

    /**
     * ===============================
     * HAPUS DATA
     * ===============================
     */
    public function destroy(SalaryDeductionRule $rule)
    {
        $rule->delete();

        return redirect()
            ->route('admin.potongan-gaji.index')
            ->with('success', 'Aturan potongan gaji berhasil dihapus');
    }

    /**
     * ===============================
     * AKTIF / NONAKTIF
     * ===============================
     */
    public function toggle(SalaryDeductionRule $rule)
    {
        $rule->update([
            'aktif' => !$rule->aktif
        ]);

        return redirect()
            ->route('admin.potongan-gaji.index')
            ->with('success', 'Status aturan berhasil diubah');
    }
}
