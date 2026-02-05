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
     * LIST ATURAN
     * ===============================
     */
    public function index()
    {
        $rules = SalaryDeductionRule::orderBy('nama')->get();
        return view('admin.potongan-gaji.index', compact('rules'));
    }

    /**
     * ===============================
     * FORM TAMBAH
     * ===============================
     */
    public function create()
    {
        $penempatans = User::whereNotNull('penempatan')
            ->distinct()
            ->orderBy('penempatan')
            ->pluck('penempatan')
            ->toArray();

        $tunjangans = [
            'tunjangan_umum'      => 'Tunjangan Umum',
            'tunjangan_transport' => 'Tunjangan Transport',
            'tunjangan_thr'       => 'Tunjangan THR',
            'tunjangan_kesehatan' => 'Tunjangan Kesehatan',
        ];

        return view('admin.potongan-gaji.create', compact(
            'penempatans',
            'tunjangans'
        ));
    }

    /**
     * ===============================
     * SIMPAN
     * ===============================
     */
    public function store(Request $request)
    {
        $data = $this->validateRule($request, true);

        SalaryDeductionRule::create($data);

        return redirect()
            ->route('admin.potongan-gaji.index')
            ->with('success', 'Aturan potongan gaji berhasil ditambahkan');
    }

    /**
     * ===============================
     * 🔥 FORM EDIT — BARU
     * ===============================
     */
    public function edit(SalaryDeductionRule $rule)
    {
        $penempatans = User::whereNotNull('penempatan')
            ->distinct()
            ->orderBy('penempatan')
            ->pluck('penempatan')
            ->toArray();

        $tunjangans = [
            'tunjangan_umum'      => 'Tunjangan Umum',
            'tunjangan_transport' => 'Tunjangan Transport',
            'tunjangan_thr'       => 'Tunjangan THR',
            'tunjangan_kesehatan' => 'Tunjangan Kesehatan',
        ];

        return view('admin.potongan-gaji.edit', compact(
            'rule',
            'penempatans',
            'tunjangans'
        ));
    }

    /**
     * ===============================
     * 🔥 UPDATE — BARU
     * ===============================
     */
    public function update(Request $request, SalaryDeductionRule $rule)
    {
        $data = $this->validateRule($request, false, $rule->id);

        $rule->update($data);

        return redirect()
            ->route('admin.potongan-gaji.index')
            ->with('success', 'Aturan potongan berhasil diperbarui');
    }

    /**
     * ===============================
     * DELETE
     * ===============================
     */
    public function destroy(SalaryDeductionRule $rule)
    {
        $rule->delete();
        return back()->with('success', 'Aturan berhasil dihapus');
    }

    /**
     * ===============================
     * TOGGLE AKTIF
     * ===============================
     */
    public function toggle(SalaryDeductionRule $rule)
    {
        $rule->update(['aktif' => !$rule->aktif]);
        return back()->with('success', 'Status aturan diubah');
    }

    /**
     * ===============================
     * VALIDATION HELPER (AMAN)
     * ===============================
     */
    private function validateRule(Request $request, bool $isCreate = true, $ignoreId = null)
    {
        $uniqueRule = $isCreate
            ? 'unique:salary_deduction_rules,kode'
            : "unique:salary_deduction_rules,kode,$ignoreId";

        $data = $request->validate([
            'kode'        => "required|string|max:50|$uniqueRule",
            'nama'        => 'required|string|max:255',
            'keterangan'  => 'nullable|string',

            'type'        => 'required|in:fixed,percentage',
            'value'       => 'required|numeric|min:0',

            'base_source' => 'required|in:gaji_pokok,tunjangan,total_gaji',
            'tunjangan_items' => 'nullable|array',

            'condition_type'  => 'required|in:pelanggaran,off_day,terlambat',
            'condition_value' => 'nullable|integer|min:1',

            'max_occurrence' => 'nullable|integer|min:1',
            'max_minutes'    => 'nullable|integer|min:1',

            'penempatan'     => 'required|array|min:1',
            'aktif'          => 'nullable|boolean',
        ]);

        // ===============================
        // NORMALISASI (tidak ubah sistem)
        // ===============================
        $data['kode'] = strtoupper($data['kode']);
        $data['condition_value'] = $data['condition_value'] ?? 1;
        $data['aktif'] = $request->boolean('aktif');

        if ($data['base_source'] !== 'tunjangan') {
            $data['tunjangan_items'] = [];
        }

        return $data;
    }
}
