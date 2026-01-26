@php
    // LIST PENEMPATAN (samakan dengan UserController)
    $penempatanList = [
        'Outlet A',
        'Outlet B',
        'Gudang',
        'Office',
    ];

    $selectedPenempatan = old(
        'penempatan',
        $rule->penempatan ?? []
    );
@endphp

{{-- KODE --}}
<div class="form-group">
    <label>Kode Aturan</label>
    <input type="text"
           name="kode"
           class="form-control"
           value="{{ old('kode', $rule->kode ?? '') }}"
           {{ isset($rule) ? 'readonly' : '' }}
           required>
</div>

{{-- NAMA --}}
<div class="form-group">
    <label>Nama Aturan</label>
    <input type="text"
           name="nama"
           class="form-control"
           value="{{ old('nama', $rule->nama ?? '') }}"
           required>
</div>

{{-- KETERANGAN --}}
<div class="form-group">
    <label>Keterangan</label>
    <textarea name="keterangan"
              class="form-control"
              rows="2">{{ old('keterangan', $rule->keterangan ?? '') }}</textarea>
</div>

<hr>

{{-- JENIS POTONGAN --}}
<div class="form-group">
    <label>Jenis Potongan</label>
    <select name="type" class="form-control" required>
        <option value="">-- Pilih --</option>
        <option value="fixed"
            {{ old('type', $rule->type ?? '') == 'fixed' ? 'selected' : '' }}>
            Nominal (Rp)
        </option>
        <option value="percentage"
            {{ old('type', $rule->type ?? '') == 'percentage' ? 'selected' : '' }}>
            Persentase (%)
        </option>
    </select>
</div>

{{-- NILAI --}}
<div class="form-group">
    <label>Nilai Potongan</label>
    <input type="number"
           step="0.01"
           name="value"
           class="form-control"
           value="{{ old('value', $rule->value ?? 0) }}"
           required>
    <small class="text-muted">
        Jika persentase → isi angka tanpa %
    </small>
</div>

{{-- BASE AMOUNT --}}
<div class="form-group">
    <label>Dihitung Dari</label>
    <select name="base_amount" class="form-control" required>
        <option value="">-- Pilih --</option>
        <option value="gaji_pokok"
            {{ old('base_amount', $rule->base_amount ?? '') == 'gaji_pokok' ? 'selected' : '' }}>
            Gaji Pokok
        </option>
        <option value="salary_kotor"
            {{ old('base_amount', $rule->base_amount ?? '') == 'salary_kotor' ? 'selected' : '' }}>
            Gaji Kotor
        </option>
        <option value="total_gaji"
            {{ old('base_amount', $rule->base_amount ?? '') == 'total_gaji' ? 'selected' : '' }}>
            Total Gaji
        </option>
    </select>
</div>

<hr>

{{-- KONDISI --}}
<div class="form-group">
    <label>Jenis Kondisi</label>
    <select name="condition_type" class="form-control" required>
        <option value="">-- Pilih --</option>
        <option value="terlambat"
            {{ old('condition_type', $rule->condition_type ?? '') == 'terlambat' ? 'selected' : '' }}>
            Terlambat
        </option>
        <option value="off_day"
            {{ old('condition_type', $rule->condition_type ?? '') == 'off_day' ? 'selected' : '' }}>
            Off / Tidak Masuk
        </option>
        <option value="pelanggaran"
            {{ old('condition_type', $rule->condition_type ?? '') == 'pelanggaran' ? 'selected' : '' }}>
            Pelanggaran
        </option>
    </select>
</div>

{{-- BATAS --}}
<div class="form-group">
    <label>Nilai Kondisi (Batas)</label>
    <input type="number"
           name="condition_value"
           class="form-control"
           value="{{ old('condition_value', $rule->condition_value ?? 0) }}">
    <small class="text-muted">
        Contoh: Terlambat 3x → isi 3
    </small>
</div>

<hr>

{{-- PENEMPATAN (MULTI CHECKBOX) --}}
<div class="form-group">
    <label class="font-weight-bold">Berlaku Untuk Penempatan</label>

    <div class="border rounded p-3">

        {{-- CHECK ALL --}}
        <div class="form-check mb-2">
            <input type="checkbox"
                   id="checkAllPenempatan"
                   class="form-check-input">
            <label for="checkAllPenempatan" class="form-check-label">
                <strong>Semua Penempatan</strong>
            </label>
        </div>

        <hr>

        <div class="row">
            @foreach($penempatanList as $p)
                <div class="col-md-4">
                    <div class="form-check">
                        <input type="checkbox"
                               name="penempatan[]"
                               value="{{ $p }}"
                               class="form-check-input penempatan-item"
                               id="penempatan_{{ $loop->index }}"
                               {{ in_array($p, $selectedPenempatan ?? []) ? 'checked' : '' }}>
                        <label for="penempatan_{{ $loop->index }}"
                               class="form-check-label">
                            {{ $p }}
                        </label>
                    </div>
                </div>
            @endforeach
        </div>

        <small class="text-muted d-block mt-2">
            Jika tidak ada yang dipilih → aturan berlaku untuk <strong>SEMUA karyawan</strong>
        </small>
    </div>
</div>

{{-- STATUS --}}
<div class="form-group form-check mt-3">
    <input type="checkbox"
           name="aktif"
           class="form-check-input"
           id="aktif"
           value="1"
           {{ old('aktif', $rule->aktif ?? true) ? 'checked' : '' }}>
    <label for="aktif" class="form-check-label">
        Aktif
    </label>
</div>

@push('scripts')
<script>
document.getElementById('checkAllPenempatan').addEventListener('change', function () {
    document.querySelectorAll('.penempatan-item').forEach(cb => {
        cb.checked = false;
    });
});
</script>
@endpush
