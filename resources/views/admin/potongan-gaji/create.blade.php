@extends('layouts.app')

@section('title','Tambah Aturan Potongan Gaji')

@section('content')
<div class="container-fluid">

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Tambah Aturan Potongan Gaji</h5>
        </div>

        <form method="POST" action="{{ route('admin.potongan-gaji.store') }}">
            @csrf

            <div class="card-body">

                {{-- ================= KODE ================= --}}
                <div class="form-group">
                    <label>Kode Aturan</label>
                    <input type="text"
                           name="kode"
                           class="form-control @error('kode') is-invalid @enderror"
                           value="{{ old('kode') }}"
                           placeholder="Contoh: TELAT_3X"
                           required>
                    @error('kode')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- ================= NAMA ================= --}}
                <div class="form-group">
                    <label>Nama Aturan</label>
                    <input type="text"
                           name="nama"
                           class="form-control @error('nama') is-invalid @enderror"
                           value="{{ old('nama') }}"
                           placeholder="Contoh: Terlambat 3 Kali"
                           required>
                    @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- ================= KETERANGAN ================= --}}
                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea name="keterangan"
                              class="form-control"
                              rows="2"
                              placeholder="Opsional">{{ old('keterangan') }}</textarea>
                </div>

                <hr>

                {{-- ================= JENIS POTONGAN ================= --}}
                <div class="form-group">
                    <label>Jenis Potongan</label>
                    <select name="type"
                            class="form-control @error('type') is-invalid @enderror"
                            required>
                        <option value="">-- Pilih --</option>
                        <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>
                            Nominal (Rp)
                        </option>
                        <option value="percentage" {{ old('type') === 'percentage' ? 'selected' : '' }}>
                            Persentase (%)
                        </option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- ================= NILAI POTONGAN ================= --}}
                <div class="form-group">
                    <label>Nilai Potongan</label>
                    <input type="number"
                           step="0.01"
                           name="value"
                           class="form-control @error('value') is-invalid @enderror"
                           value="{{ old('value') }}"
                           required>
                    @error('value')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- ================= DASAR PERHITUNGAN ================= --}}
                <div class="form-group">
                    <label>Dihitung Dari</label>
                    <select name="base_amount"
                            class="form-control @error('base_amount') is-invalid @enderror"
                            required>
                        <option value="">-- Pilih --</option>
                        <option value="gaji_pokok" {{ old('base_amount') === 'gaji_pokok' ? 'selected' : '' }}>
                            Gaji Pokok
                        </option>
                        <option value="salary_kotor" {{ old('base_amount') === 'salary_kotor' ? 'selected' : '' }}>
                            Gaji Kotor
                        </option>
                        <option value="total_gaji" {{ old('base_amount') === 'total_gaji' ? 'selected' : '' }}>
                            Total Gaji
                        </option>
                    </select>
                    @error('base_amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr>

                {{-- ================= KONDISI ================= --}}
                <div class="form-group">
                    <label>Jenis Kondisi</label>
                    <select name="condition_type"
                            class="form-control @error('condition_type') is-invalid @enderror"
                            required>
                        <option value="">-- Pilih --</option>
                        <option value="terlambat" {{ old('condition_type') === 'terlambat' ? 'selected' : '' }}>
                            Terlambat
                        </option>
                        <option value="off_day" {{ old('condition_type') === 'off_day' ? 'selected' : '' }}>
                            Off / Tidak Masuk
                        </option>
                        <option value="pelanggaran" {{ old('condition_type') === 'pelanggaran' ? 'selected' : '' }}>
                            Pelanggaran
                        </option>
                    </select>
                    @error('condition_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- ================= NILAI KONDISI ================= --}}
                <div class="form-group">
                    <label>Nilai Kondisi (Batas)</label>
                    <input type="number"
                           name="condition_value"
                           class="form-control"
                           value="{{ old('condition_value', 0) }}"
                           min="0">
                </div>

                <hr>

                {{-- ================= PENEMPATAN ================= --}}
                <div class="form-group">
                    <label class="font-weight-bold">Berlaku Untuk Penempatan</label>

                    <div class="border rounded p-3">

                        <div class="form-check mb-2">
                            <input type="checkbox" id="checkAll" class="form-check-input">
                            <label for="checkAll" class="form-check-label">
                                <strong>Semua Penempatan</strong>
                            </label>
                        </div>

                        <hr>

                        <div class="row">
                            @forelse($penempatans as $p)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input type="checkbox"
                                               name="penempatan[]"
                                               value="{{ $p }}"
                                               class="form-check-input penempatan-item"
                                               id="p_{{ $loop->index }}"
                                               {{ in_array($p, old('penempatan', [])) ? 'checked' : '' }}>
                                        <label for="p_{{ $loop->index }}" class="form-check-label">
                                            {{ $p }}
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-muted">
                                    Belum ada penempatan karyawan
                                </div>
                            @endforelse
                        </div>

                        <small class="text-muted d-block mt-2">
                            Jika tidak dipilih → aturan berlaku untuk semua karyawan
                        </small>
                    </div>
                </div>

                {{-- ================= STATUS ================= --}}
                <div class="form-group form-check">
                    <input type="checkbox"
                           name="aktif"
                           class="form-check-input"
                           id="aktif"
                           value="1"
                           {{ old('aktif', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="aktif">
                        Aktif
                    </label>
                </div>

            </div>

            <div class="card-footer text-right">
                <a href="{{ route('admin.potongan-gaji.index') }}" class="btn btn-secondary">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    Simpan Aturan
                </button>
            </div>

        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.getElementById('checkAll')?.addEventListener('change', function () {
    document.querySelectorAll('.penempatan-item').forEach(cb => {
        cb.checked = this.checked;
    });
});
</script>
@endpush
