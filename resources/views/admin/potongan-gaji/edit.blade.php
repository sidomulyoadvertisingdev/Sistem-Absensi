@extends('layouts.app')

@section('title','Edit Aturan Potongan Gaji')

@section('content')
<div class="container-fluid">

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 font-weight-bold">Edit Aturan Potongan Gaji</h5>
        </div>

        <form method="POST" action="{{ route('admin.potongan-gaji.update', $rule->id) }}">
            @csrf
            @method('PUT')

            <div class="card-body">
                @php
                    $selectedBaseSource = old(
                        'base_source',
                        $rule->base_source
                            ?? (($rule->base_amount ?? null) === 'salary_kotor'
                                ? 'total_gaji'
                                : ($rule->base_amount ?? 'gaji_pokok'))
                    );

                    $selectedTunjanganItems = old(
                        'tunjangan_items',
                        is_array($rule->tunjangan_items)
                            ? $rule->tunjangan_items
                            : (json_decode($rule->tunjangan_items ?? '[]', true) ?? [])
                    );

                    $rulePenempatan = is_array($rule->penempatan)
                        ? $rule->penempatan
                        : json_decode($rule->penempatan ?? '[]', true) ?? [];
                @endphp

                {{-- ================= KODE ================= --}}
                <div class="form-group">
                    <label>Kode Aturan</label>
                    <input type="text"
                           name="kode"
                           class="form-control"
                           value="{{ old('kode', $rule->kode) }}"
                           readonly>
                </div>

                {{-- ================= NAMA ================= --}}
                <div class="form-group">
                    <label>Nama Aturan</label>
                    <input type="text"
                           name="nama"
                           class="form-control"
                           value="{{ old('nama', $rule->nama) }}"
                           required>
                </div>

                {{-- ================= KETERANGAN ================= --}}
                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea name="keterangan"
                              class="form-control"
                              rows="2">{{ old('keterangan', $rule->keterangan) }}</textarea>
                </div>

                <hr>

                {{-- ================= JENIS POTONGAN ================= --}}
                <div class="form-group">
                    <label>Jenis Potongan</label>
                    <select name="type" class="form-control" required>
                        <option value="">-- Pilih --</option>
                        <option value="fixed" {{ old('type', $rule->type) === 'fixed' ? 'selected' : '' }}>
                            Nominal (Rp)
                        </option>
                        <option value="percentage" {{ old('type', $rule->type) === 'percentage' ? 'selected' : '' }}>
                            Persentase (%)
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Nilai Potongan</label>
                    <input type="number"
                           step="0.01"
                           name="value"
                           class="form-control"
                           value="{{ old('value', $rule->value) }}"
                           required>
                </div>

                <hr>

                {{-- ================= DASAR POTONGAN ================= --}}
                <div class="form-group">
                    <label>Potongan Dihitung Dari</label>
                    <select name="base_source"
                            id="base_source"
                            class="form-control"
                            required>
                        <option value="">-- Pilih --</option>
                        <option value="gaji_pokok" {{ $selectedBaseSource === 'gaji_pokok' ? 'selected' : '' }}>
                            Gaji Pokok
                        </option>
                        <option value="tunjangan" {{ $selectedBaseSource === 'tunjangan' ? 'selected' : '' }}>
                            Tunjangan
                        </option>
                        <option value="total_gaji" {{ $selectedBaseSource === 'total_gaji' ? 'selected' : '' }}>
                            Total Gaji
                        </option>
                    </select>
                </div>

                {{-- ================= PILIH TUNJANGAN ================= --}}
                <div class="form-group {{ $selectedBaseSource !== 'tunjangan' ? 'd-none' : '' }}" id="tunjangan-box">
                    <label>Jenis Tunjangan yang Dipotong</label>
                    <div class="border rounded p-3">
                        @forelse($tunjangans as $key => $label)
                            <div class="form-check">
                                <input type="checkbox"
                                       name="tunjangan_items[]"
                                       value="{{ $key }}"
                                       class="form-check-input"
                                       id="tj_{{ $key }}"
                                       {{ in_array($key, $selectedTunjanganItems ?? [], true) ? 'checked' : '' }}>
                                <label for="tj_{{ $key }}" class="form-check-label">
                                    {{ $label }}
                                </label>
                            </div>
                        @empty
                            <div class="text-muted">
                                Belum ada tunjangan di struktur gaji
                            </div>
                        @endforelse
                    </div>

                    <small class="text-muted">
                        Tunjangan diambil dari struktur gaji karyawan
                    </small>
                </div>

                <hr>

                {{-- ================= KONDISI ================= --}}
                <div class="form-group">
                    <label>Jenis Kondisi</label>
                    <select name="condition_type" class="form-control" required>
                        <option value="">-- Pilih --</option>
                        <option value="terlambat" {{ old('condition_type', $rule->condition_type) === 'terlambat' ? 'selected' : '' }}>
                            Terlambat
                        </option>
                        <option value="off_day" {{ old('condition_type', $rule->condition_type) === 'off_day' ? 'selected' : '' }}>
                            Tidak Masuk
                        </option>
                        <option value="pelanggaran" {{ old('condition_type', $rule->condition_type) === 'pelanggaran' ? 'selected' : '' }}>
                            Pelanggaran
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Trigger Minimal Kejadian</label>
                    <input type="number"
                           name="condition_value"
                           class="form-control"
                           value="{{ old('condition_value', $rule->condition_value ?? 1) }}"
                           min="1">
                </div>

                <hr>

                {{-- ================= PENEMPATAN ================= --}}
                <div class="form-group">
                    <label class="font-weight-bold">Berlaku Untuk Penempatan</label>

                    <div class="border rounded p-3">
                        <div class="row">
                            @forelse($penempatans as $p)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input type="checkbox"
                                               name="penempatan[]"
                                               value="{{ $p }}"
                                               class="form-check-input"
                                               id="p_{{ $loop->index }}"
                                               {{ in_array($p, old('penempatan', $rulePenempatan), true) ? 'checked' : '' }}>
                                        <label for="p_{{ $loop->index }}" class="form-check-label">
                                            {{ $p }}
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-muted">
                                    Tidak ada penempatan
                                </div>
                            @endforelse
                        </div>

                        <small class="text-muted d-block mt-2">
                            Pilih minimal satu penempatan (validasi di server)
                        </small>
                        @error('penempatan')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- ================= STATUS ================= --}}
                <div class="form-group form-check">
                    <input type="checkbox"
                           name="aktif"
                           value="1"
                           class="form-check-input"
                           id="aktif"
                           {{ old('aktif', $rule->aktif) ? 'checked' : '' }}>
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
const baseSelect = document.getElementById('base_source');
const tunjanganBox = document.getElementById('tunjangan-box');

function toggleTunjangan() {
    tunjanganBox.classList.toggle(
        'd-none',
        baseSelect.value !== 'tunjangan'
    );
}

baseSelect.addEventListener('change', toggleTunjangan);
toggleTunjangan();
</script>
@endpush
