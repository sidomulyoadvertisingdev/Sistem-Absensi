<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalaryDeductionRule;
use App\Models\User;
use App\Models\UserSalary;
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
        /**
         * ===============================
         * PENEMPATAN
         * ===============================
         */
        $penempatans = User::whereNotNull('penempatan')
            ->distinct()
            ->orderBy('penempatan')
            ->pluck('penempatan')
            ->toArray();

        /**
         * ===============================
         * JENIS TUNJANGAN (DARI USER_SALARIES)
         * ===============================
         * HARD SOURCE OF TRUTH
         */
        $tunjangans = [
            'tunjangan_umum'       => 'Tunjangan Umum',
            'tunjangan_transport'  => 'Tunjangan Transport',
            'tunjangan_thr'        => 'Tunjangan THR',
            'tunjangan_kesehatan'  => 'Tunjangan Kesehatan',
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
        $data = $request->validate([
            'kode'        => 'required|string|max:50|unique:salary_deduction_rules,kode',
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
        // NORMALISASI
        // ===============================
        $data['kode'] = strtoupper($data['kode']);
        $data['condition_value'] = $data['condition_value'] ?? 1;
        $data['aktif'] = $request->boolean('aktif');

        // 🔥 hanya simpan tunjangan jika base = tunjangan
        if ($data['base_source'] !== 'tunjangan') {
            $data['tunjangan_items'] = [];
        }

        SalaryDeductionRule::create($data);

        return redirect()
            ->route('admin.potongan-gaji.index')
            ->with('success', 'Aturan potongan gaji berhasil ditambahkan');
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
}
