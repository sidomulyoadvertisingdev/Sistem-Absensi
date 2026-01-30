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

                {{-- ================= KODE ================= --}}
                <div class="form-group">
                    <label>Kode Aturan</label>
                    <input type="text"
                           class="form-control"
                           value="{{ $rule->kode }}"
                           readonly>
                </div>

                {{-- ================= NAMA ================= --}}
                <div class="form-group">
                    <label>Nama Aturan</label>
                    <input type="text"
                           name="nama"
                           class="form-control @error('nama') is-invalid @enderror"
                           value="{{ old('nama', $rule->nama) }}"
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
                              rows="2">{{ old('keterangan', $rule->keterangan) }}</textarea>
                </div>

                <hr>

                {{-- ================= JENIS POTONGAN ================= --}}
                <div class="form-group">
                    <label>Jenis Potongan</label>
                    <select name="type"
                            class="form-control @error('type') is-invalid @enderror"
                            required>
                        <option value="fixed" {{ old('type', $rule->type) === 'fixed' ? 'selected' : '' }}>
                            Nominal (Rp)
                        </option>
                        <option value="percentage" {{ old('type', $rule->type) === 'percentage' ? 'selected' : '' }}>
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
                           value="{{ old('value', $rule->value) }}"
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
                        <option value="gaji_pokok" {{ old('base_amount', $rule->base_amount) === 'gaji_pokok' ? 'selected' : '' }}>
                            Gaji Pokok
                        </option>
                        <option value="salary_kotor" {{ old('base_amount', $rule->base_amount) === 'salary_kotor' ? 'selected' : '' }}>
                            Salary Kotor
                        </option>
                        <option value="total_gaji" {{ old('base_amount', $rule->base_amount) === 'total_gaji' ? 'selected' : '' }}>
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
                        <option value="terlambat" {{ old('condition_type', $rule->condition_type) === 'terlambat' ? 'selected' : '' }}>
                            Terlambat
                        </option>
                        <option value="off_day" {{ old('condition_type', $rule->condition_type) === 'off_day' ? 'selected' : '' }}>
                            Off / Tidak Masuk
                        </option>
                        <option value="pelanggaran" {{ old('condition_type', $rule->condition_type) === 'pelanggaran' ? 'selected' : '' }}>
                            Pelanggaran
                        </option>
                    </select>
                    @error('condition_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- ================= TRIGGER ================= --}}
                <div class="form-group">
                    <label>Minimal Terjadi (Trigger)</label>
                    <input type="number"
                           name="condition_value"
                           class="form-control"
                           value="{{ old('condition_value', $rule->condition_value) }}"
                           min="0">
                </div>

                {{-- ================= BATASAN ================= --}}
                <div class="form-group">
                    <label>Maksimal Jumlah Kejadian</label>
                    <input type="number"
                           name="max_occurrence"
                           class="form-control"
                           value="{{ old('max_occurrence', $rule->max_occurrence) }}"
                           min="1">
                    <small class="text-muted">Kosongkan jika tidak dibatasi</small>
                </div>

                <div class="form-group">
                    <label>Maksimal Menit per Kejadian</label>
                    <input type="number"
                           name="max_minutes"
                           class="form-control"
                           value="{{ old('max_minutes', $rule->max_minutes) }}"
                           min="1">
                    <small class="text-muted">Kosongkan jika tidak dibatasi</small>
                </div>

                <hr>

                {{-- ================= PENEMPATAN ================= --}}
                @php
                    $rulePenempatan = is_array($rule->penempatan)
                        ? $rule->penempatan
                        : json_decode($rule->penempatan, true) ?? [];
                @endphp

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
                                               {{ in_array($p, old('penempatan', $rulePenempatan)) ? 'checked' : '' }}>
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
                            Jika tidak dipilih → aturan berlaku global
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
                           {{ old('aktif', $rule->aktif) ? 'checked' : '' }}>
                    <label class="form-check-label" for="aktif">
                        Aktif
                    </label>
                </div>

            </div>

            <div class="card-footer text-right">
                <button class="btn btn-primary">
                    <i class="fas fa-save"></i> Update
                </button>
                <a href="{{ route('admin.potongan-gaji.index') }}" class="btn btn-secondary">
                    Batal
                </a>
            </div>

        </form>
    </div>

</div>
@endsection
